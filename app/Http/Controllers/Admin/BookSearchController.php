<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use App\Models\Book; // Import the Book model

class BookSearchController extends Controller
{
    public function searchByImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if (!$request->hasFile('image')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No image uploaded.',
            ], 400);
        }

        $image = $request->file('image');
        $imagePath = $image->store('tmp', 'public'); // Store in storage/app/public/tmp
        $fullImagePath = Storage::disk('public')->path($imagePath);

        try {
            $imageAnnotator = new ImageAnnotatorClient();
            $imageContent = file_get_contents($fullImagePath);
            $response = $imageAnnotator->detectText($imageContent);
            $texts = $response->getTextAnnotations();

            $extractedText = '';
            if (!empty($texts)) {
                $extractedText = $texts[0]->getDescription();
            }

            $bookAttributes = $this->parseBookAttributes($extractedText);

            $imageAnnotator->close();

            // Clean up the temporarily stored image
            Storage::disk('public')->delete($imagePath);

            $foundBooks = collect();

            // 1. Search by ISBN (most reliable)
            if ($bookAttributes['isbn']) {
                $books = Book::where('isbn', $bookAttributes['isbn'])->get();
                if ($books->isNotEmpty()) {
                    $foundBooks = $foundBooks->merge($books);
                }
            }

            // 2. Search by Title Candidates
            if ($bookAttributes['title_candidates']->isNotEmpty()) {
                foreach ($bookAttributes['title_candidates'] as $title) {
                    // Split title into words and search for books matching any significant words
                    $keywords = collect(explode(' ', $title))
                                ->filter(fn($word) => strlen($word) > 2) // Filter out short words
                                ->unique()
                                ->values();

                    if ($keywords->isNotEmpty()) {
                        $books = Book::where(function ($query) use ($keywords) {
                            foreach ($keywords as $keyword) {
                                $query->orWhere('title', 'like', '%' . $keyword . '%');
                            }
                        })->get();
                        $foundBooks = $foundBooks->merge($books);
                    }
                }
            }
            
            // 3. Search by Author Candidates
            if ($bookAttributes['author_candidates']->isNotEmpty() && $foundBooks->isEmpty()) { // Only if no books found yet
                foreach ($bookAttributes['author_candidates'] as $author) {
                     $keywords = collect(explode(' ', $author))
                                ->filter(fn($word) => strlen($word) > 2) // Filter out short words
                                ->unique()
                                ->values();

                    if ($keywords->isNotEmpty()) {
                        $books = Book::where(function ($query) use ($keywords) {
                            foreach ($keywords as $keyword) {
                                $query->orWhere('author', 'like', '%' . $keyword . '%');
                            }
                        })->get();
                         $foundBooks = $foundBooks->merge($books);
                    }
                }
            }

            // Ensure unique books and eager load cover image if applicable
            $foundBooks = $foundBooks->unique('id')->values()->map(function ($book) {
                // Assuming 'cover_image' stores a path relative to 'storage/app/public'
                // and you want a full URL for the frontend.
                $book->cover_image_url = $book->cover_image ? Storage::url($book->cover_image) : null;
                return $book;
            });


            return response()->json([
                'status' => 'success',
                'message' => 'Image processed and search performed.',
                'extracted_attributes' => $bookAttributes,
                'books' => $foundBooks,
            ]);

        } catch (\Exception $e) {
            // Clean up the temporarily stored image even on error
            Storage::disk('public')->delete($fullImagePath); // Use $fullImagePath here

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing image or searching books: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parses the extracted text to find potential book attributes.
     *
     * @param string $text
     * @return array
     */
    private function parseBookAttributes(string $text): array
    {
        $attributes = [
            'isbn' => null,
            'title_candidates' => collect(), // Use Laravel collections
            'author_candidates' => collect(), // Use Laravel collections
        ];

        // Normalize text for easier parsing
        $normalizedText = str_replace(['\n', '\r'], ' ', $text);
        $lines = explode("\n", $text);

        // 1. ISBN Extraction (most reliable)
        // ISBN-13: 978-X-XX-XXXXXX-X or 978XXXXXXXXXX
        // ISBN-10: X-XX-XXXXXX-X or XXXXXXXXX
        $isbnPattern = '/(?:ISBN(?:-13)?:?\s*(?=[0-9]{13}))?((97[89][ -]?[0-9]{1,5}[ -]?[0-9]{1,7}[ -]?[0-9]{1,6}[ -]?[0-9])|(?:ISBN(?:-10)?:?\s*(?=[0-9]{10}))?([0-9]{1,5}[ -]?[0-9]{1,7}[ -]?[0-9]{1,6}[ -]?[0-9X]))/';
        if (preg_match($isbnPattern, $normalizedText, $matches)) {
            // Take the first full match and clean it
            $isbn = preg_replace('/[^0-9X]/', '', $matches[0]);
            // Ensure it's a valid ISBN length after stripping
            if (strlen($isbn) == 10 || strlen($isbn) == 13) {
                $attributes['isbn'] = $isbn;
            }
        }

        // 2. Title and Author Candidates (less reliable, heuristic)
        // This is highly heuristic. A more robust solution would involve NLP.
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || strlen($trimmedLine) < 3) {
                continue;
            }

            // Simple heuristics for authors: words "by", "author", or looks like a name
            if (preg_match('/^by\s(.+)/i', $trimmedLine, $authorMatch)) {
                $attributes['author_candidates']->push(trim($authorMatch[1]));
            } elseif (preg_match('/^(author|penulis):\s*(.+)/i', $trimmedLine, $authorMatch)) {
                $attributes['author_candidates']->push(trim($authorMatch[2]));
            } elseif (str_word_count($trimmedLine) >= 2 && str_word_count($trimmedLine) <= 5 && preg_match('/^[A-Z][a-zA-Z\s\.\'-]+$/', $trimmedLine)) {
                // Lines with 2-5 words, starting with capital letter, look like names (potential author)
                $attributes['author_candidates']->push($trimmedLine);
            } else {
                // Treat other reasonable-length lines as title candidates
                $attributes['title_candidates']->push($trimmedLine);
            }
        }

        // Filter out duplicates
        $attributes['title_candidates'] = $attributes['title_candidates']->unique()->values();
        $attributes['author_candidates'] = $attributes['author_candidates']->unique()->values();

        return $attributes;
    }
}
