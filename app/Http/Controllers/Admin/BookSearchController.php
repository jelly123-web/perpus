<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookSearchController extends Controller
{
    public function show(): View
    {
        return view('admin.books.search-by-image');
    }

    public function searchByImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        if (! $request->hasFile('image')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada gambar yang diunggah.',
            ], 400);
        }

        $image = $request->file('image');
        $imagePath = $image->store('tmp/book-search', 'public');
        $fullImagePath = Storage::disk('public')->path($imagePath);
        $preparedImagePath = null;

        try {
            $preparedImagePath = $this->prepareImageForMatching($fullImagePath) ?? $fullImagePath;
            $bookAttributes = $this->extractBookAttributes($preparedImagePath);
            $uploadedSignature = $this->buildImageSignature($fullImagePath);
            $books = $this->findStrictImageMatch($uploadedSignature, $bookAttributes);

            $user = $request->user();
            $found = $books->isNotEmpty();

            return response()->json([
                'status' => 'success',
                'message' => $found
                    ? 'Buku yang sama sudah ditemukan.'
                    : 'Buku tidak ditemukan.',
                'extracted_attributes' => $bookAttributes,
                'books' => $books->map(fn (array $book) => $this->decorateSearchResultForUser($book, $user))->values(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses gambar: '.$e->getMessage(),
            ], 500);
        } finally {
            Storage::disk('public')->delete($imagePath);
            if ($preparedImagePath && $preparedImagePath !== $fullImagePath && is_file($preparedImagePath)) {
                @unlink($preparedImagePath);
            }
        }
    }

    private function extractBookAttributes(string $fullImagePath): array
    {
        $text = $this->extractTextFromImage($fullImagePath);

        $attributes = [
            'isbn' => null,
            'title_candidates' => collect(),
            'author_candidates' => collect(),
            'raw_text' => $text,
        ];

        $normalizedText = str_replace(["\n", "\r"], ' ', $text);
        $lines = preg_split("/\r\n|\r|\n/", $text) ?: [];

        $isbnPattern = '/(?:ISBN(?:-13)?:?\s*(?=[0-9]{13}))?((97[89][ -]?[0-9]{1,5}[ -]?[0-9]{1,7}[ -]?[0-9]{1,6}[ -]?[0-9])|(?:ISBN(?:-10)?:?\s*(?=[0-9]{10}))?([0-9]{1,5}[ -]?[0-9]{1,7}[ -]?[0-9]{1,6}[ -]?[0-9X]))/';
        if (preg_match($isbnPattern, $normalizedText, $matches)) {
            $isbn = preg_replace('/[^0-9X]/', '', $matches[0]);

            if (strlen($isbn) === 10 || strlen($isbn) === 13) {
                $attributes['isbn'] = $isbn;
            }
        }

        foreach ($lines as $line) {
            $trimmedLine = trim((string) $line);

            if ($trimmedLine === '' || Str::length($trimmedLine) < 3) {
                continue;
            }

            if (preg_match('/^by\s+(.+)/i', $trimmedLine, $authorMatch)) {
                $attributes['author_candidates']->push(trim($authorMatch[1]));
            } elseif (preg_match('/^(author|penulis):\s*(.+)/i', $trimmedLine, $authorMatch)) {
                $attributes['author_candidates']->push(trim($authorMatch[2]));
            } elseif (str_word_count($trimmedLine) >= 2 && str_word_count($trimmedLine) <= 5 && preg_match('/^[A-Z][a-zA-Z\s\.\'-]+$/', $trimmedLine)) {
                $attributes['author_candidates']->push($trimmedLine);
            } else {
                $attributes['title_candidates']->push($trimmedLine);
            }
        }

        $attributes['title_candidates'] = $attributes['title_candidates']->unique()->values();
        $attributes['author_candidates'] = $attributes['author_candidates']->unique()->values();

        return $attributes;
    }

    private function extractTextFromImage(string $fullImagePath): string
    {
        if (! class_exists(\Google\Cloud\Vision\V1\ImageAnnotatorClient::class)) {
            return '';
        }

        $imageAnnotator = null;

        try {
            $imageAnnotator = new \Google\Cloud\Vision\V1\ImageAnnotatorClient();
            $imageContent = file_get_contents($fullImagePath);

            if ($imageContent === false) {
                return '';
            }

            $response = $imageAnnotator->detectText($imageContent);
            $texts = $response->getTextAnnotations();
            $imageAnnotator->close();

            return ! empty($texts) ? (string) $texts[0]->getDescription() : '';
        } catch (\Throwable) {
            return '';
        } finally {
            if ($imageAnnotator) {
                $imageAnnotator->close();
            }
        }
    }

    private function findStrictImageMatch(?string $uploadedSignature, array $bookAttributes): Collection
    {
        $books = Book::query()
            ->with('category')
            ->whereNotNull('cover_image')
            ->get();

        if ($books->isEmpty()) {
            return collect();
        }

        $user = request()->user();
        $normalizedIsbn = $this->normalizeIsbn($bookAttributes['isbn'] ?? null);

        if ($normalizedIsbn !== null) {
            $isbnMatch = $books->first(function (Book $book) use ($normalizedIsbn): bool {
                return $this->normalizeIsbn($book->isbn) === $normalizedIsbn;
            });

            if ($isbnMatch) {
                return collect([$this->formatSearchResult($isbnMatch, 100, 0, $user)]);
            }
        }

        $textMatch = $this->findTextMatch($books, $bookAttributes, $user);

        if ($textMatch->isNotEmpty()) {
            return $textMatch;
        }

        $bestMatch = null;
        $bestDistance = null;
        $signatureLength = strlen((string) $uploadedSignature);

        if ($uploadedSignature === null || $signatureLength === 0) {
            return collect();
        }

        foreach ($books as $book) {
            $bookSignature = $book->cover_image ? $this->buildImageSignature(Storage::disk('public')->path($book->cover_image)) : null;

            if ($bookSignature === null || strlen($bookSignature) !== $signatureLength) {
                continue;
            }

            $distance = $this->hammingDistance($uploadedSignature, $bookSignature);

            if ($bestDistance === null || $distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $book;
            }
        }

        if (! $bestMatch || $bestDistance === null) {
            return collect();
        }

        $maxDistance = (int) round($signatureLength * 0.35); // Relax back to 0.35 since 16x16 signature is much stricter inherently

        if ($bestDistance > $maxDistance) {
            return collect();
        }

        $matchScore = round(100 - (($bestDistance / max($signatureLength, 1)) * 100), 2);

        return collect([$this->formatSearchResult($bestMatch, $matchScore, $bestDistance, $user)]);
    }

    private function findTextMatch(Collection $books, array $bookAttributes, ?User $user): Collection
    {
        $titleCandidates = collect($bookAttributes['title_candidates'] ?? [])
            ->map(fn (string $candidate) => $this->normalizeSearchText($candidate))
            ->filter()
            ->values();
        $authorCandidates = collect($bookAttributes['author_candidates'] ?? [])
            ->map(fn (string $candidate) => $this->normalizeSearchText($candidate))
            ->filter()
            ->values();

        if ($titleCandidates->isEmpty() && $authorCandidates->isEmpty()) {
            return collect();
        }

        $bestBook = null;
        $bestScore = 0;

        foreach ($books as $book) {
            $title = $this->normalizeSearchText($book->title);
            $author = $this->normalizeSearchText($book->author);
            $score = 0;

            foreach ($titleCandidates as $candidate) {
                if ($candidate === '' || $title === '') {
                    continue;
                }

                if ($candidate === $title) {
                    $score += 100;
                    continue;
                }

                if (Str::contains($title, $candidate) || Str::contains($candidate, $title)) {
                    $score += 70 + min(Str::length($candidate), Str::length($title));
                }
            }

            foreach ($authorCandidates as $candidate) {
                if ($candidate === '' || $author === '') {
                    continue;
                }

                if ($candidate === $author) {
                    $score += 45;
                    continue;
                }

                if (Str::contains($author, $candidate) || Str::contains($candidate, $author)) {
                    $score += 30 + min(Str::length($candidate), Str::length($author));
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestBook = $book;
            }
        }

        if (! $bestBook || $bestScore < 30) { // Reduced threshold from 40 to 30 to allow partial text matches (e.g. "Pendidikan Antikorupsi" finding "Pendidikan antikorupsi")
            return collect();
        }

        return collect([$this->formatSearchResult($bestBook, min(100, (float) $bestScore), 0, $user)]);
    }

    private function normalizeIsbn(mixed $isbn): ?string
    {
        if (! filled($isbn)) {
            return null;
        }

        $normalized = preg_replace('/[^0-9X]/', '', Str::upper((string) $isbn));

        return in_array(strlen($normalized), [10, 13], true) ? $normalized : null;
    }

    private function normalizeSearchText(mixed $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish()
            ->value();
    }

    private function buildImageSignature(string $fullImagePath): ?string
    {
        $imageData = @file_get_contents($fullImagePath);

        if ($imageData === false) {
            return null;
        }

        $source = @imagecreatefromstring($imageData);

        if ($source === false) {
            return null;
        }

        $cropped = $this->cropImageToContent($source);
        if (is_array($cropped)) {
            [$workingImage, $isCropped] = $cropped;
        } else {
            $workingImage = $source;
            $isCropped = false;
        }

        $size = 16; // Increased resolution from 8 to 16 to create a more distinct 256-bit signature instead of 64-bit, drastically reducing false positives
        $resized = imagecreatetruecolor($size, $size);

        if ($resized === false) {
            imagedestroy($source);
            if (isset($workingImage) && $workingImage !== $source) {
                imagedestroy($workingImage);
            }

            return null;
        }

        imagecopyresampled(
            $resized,
            $workingImage,
            0,
            0,
            0,
            0,
            $size,
            $size,
            imagesx($workingImage),
            imagesy($workingImage)
        );

        $pixels = [];
        $total = 0;

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;
                $gray = (int) round(($red * 0.299) + ($green * 0.587) + ($blue * 0.114));

                $pixels[] = $gray;
                $total += $gray;
            }
        }

        imagedestroy($source);
        if ($workingImage !== $source) {
            imagedestroy($workingImage);
        }
        imagedestroy($resized);

        if ($pixels === []) {
            return null;
        }

        $average = $total / count($pixels);

        return collect($pixels)
            ->map(fn (int $gray) => $gray >= $average ? '1' : '0')
            ->implode('');
    }

    private function prepareImageForMatching(string $fullImagePath): ?string
    {
        $imageData = @file_get_contents($fullImagePath);

        if ($imageData === false) {
            return null;
        }

        $source = @imagecreatefromstring($imageData);

        if ($source === false) {
            return null;
        }

        $cropped = $this->cropImageToContent($source);

        if (! is_array($cropped)) {
            imagedestroy($source);

            return null;
        }

        [$workingImage] = $cropped;
        if ($workingImage === $source) {
            imagedestroy($source);

            return null;
        }

        $directory = storage_path('app/tmp/book-search');
        if (! is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }

        $tempPath = $directory.'/'.Str::uuid()->toString().'.png';

        try {
            imagepng($workingImage, $tempPath);
        } catch (\Throwable) {
            $tempPath = null;
        }

        imagedestroy($source);
        imagedestroy($workingImage);

        return $tempPath && is_file($tempPath) ? $tempPath : null;
    }

    /**
     * Crop away near-white margins so screenshot uploads behave like the actual cover.
     *
     * @return array{0:\GdImage|\GdImage,1:bool}|null
     */
    private function cropImageToContent($source): ?array
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $threshold = 230; // Reduced from 245 to catch slightly off-white backgrounds in screenshots
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        $stepX = max(1, (int) floor($width / 900));
        $stepY = max(1, (int) floor($height / 900));

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                $rgb = imagecolorat($source, $x, $y);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;

                if ($red >= $threshold && $green >= $threshold && $blue >= $threshold) {
                    continue;
                }

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < 0 || $maxY < 0) {
            return [$source, false];
        }

        $paddingX = max(8, (int) round($width * 0.04));
        $paddingY = max(8, (int) round($height * 0.04));

        $minX = max(0, $minX - $paddingX);
        $minY = max(0, $minY - $paddingY);
        $maxX = min($width - 1, $maxX + $paddingX);
        $maxY = min($height - 1, $maxY + $paddingY);

        $cropWidth = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;

        if ($cropWidth <= 0 || $cropHeight <= 0) {
            return [$source, false];
        }

        $coversAlmostAllImage = $cropWidth >= ($width * 0.95) && $cropHeight >= ($height * 0.95);
        if ($coversAlmostAllImage) {
            return [$source, false];
        }

        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);

        if ($cropped === false) {
            return [$source, false];
        }

        imagecopy($cropped, $source, 0, 0, $minX, $minY, $cropWidth, $cropHeight);

        return [$cropped, true];
    }

    private function hammingDistance(string $left, string $right): int
    {
        $distance = 0;
        $length = min(strlen($left), strlen($right));

        for ($i = 0; $i < $length; $i++) {
            if ($left[$i] !== $right[$i]) {
                $distance++;
            }
        }

        return $distance + abs(strlen($left) - strlen($right));
    }

    private function formatSearchResult(Book $book, float $matchScore, ?int $distance, ?User $user): array
    {
        $stockAvailable = (int) ($book->stock_available ?? 0);
        $borrowState = $this->resolveBorrowStateForUser($book, $user);
        $categoryName = $book->category?->name;

        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'category' => [
                'id' => $book->category?->id,
                'name' => $categoryName,
            ],
            'category_name' => $categoryName,
            'cover_image_url' => $book->cover_image ? Storage::disk('public')->url($book->cover_image) : null,
            'cover_url' => $book->cover_image ? Storage::disk('public')->url($book->cover_image) : null,
            'match_score' => $matchScore,
            'image_distance' => $distance,
            'stock_available' => $stockAvailable,
            'stock_total' => (int) ($book->stock_total ?? 0),
            'stock' => $stockAvailable,
            'borrow_state' => $borrowState,
            'can_borrow' => $borrowState === 'available',
            'borrowed_at' => now()->toDateString(),
            'due_at' => now()->addDay()->toDateString(),
        ];
    }

    private function decorateSearchResultForUser(array $book, ?User $user): array
    {
        $book['borrow_state'] = $book['borrow_state'] ?? $this->resolveBorrowStateFromArray($book, $user);
        $book['can_borrow'] = $book['can_borrow'] ?? ($book['borrow_state'] === 'available');
        $book['stock'] = $book['stock'] ?? (int) ($book['stock_available'] ?? 0);
        $book['cover_url'] = $book['cover_url'] ?? ($book['cover_image_url'] ?? null);
        $book['category_name'] = $book['category_name'] ?? ($book['category']['name'] ?? null);
        $book['borrowed_at'] = $book['borrowed_at'] ?? now()->toDateString();
        $book['due_at'] = $book['due_at'] ?? now()->addDay()->toDateString();

        return $book;
    }

    private function resolveBorrowStateFromArray(array $book, ?User $user): string
    {
        if (! $user || ! $user->hasPermission('view_borrower_history')) {
            return ((int) ($book['stock_available'] ?? $book['stock'] ?? 0)) > 0 ? 'available' : 'unavailable';
        }

        return $this->resolveBorrowStateForUser(
            tap(new Book(), function (Book $model) use ($book): void {
                $model->id = (int) ($book['id'] ?? 0);
                $model->stock_available = (int) ($book['stock_available'] ?? $book['stock'] ?? 0);
            }),
            $user
        );
    }

    private function resolveBorrowStateForUser(Book $book, ?User $user): string
    {
        $stockAvailable = (int) ($book->stock_available ?? 0);

        if (! $user || ! $user->hasPermission('view_borrower_history')) {
            return $stockAvailable > 0 ? 'available' : 'unavailable';
        }

        $today = Carbon::today()->toDateString();
        $activeSanction = Sanction::query()
            ->where('member_id', $user->id)
            ->where('type', 'suspend_borrowing')
            ->where('status', 'active')
            ->where(function ($query) use ($today): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->exists();

        if ($activeSanction) {
            return 'sanctioned';
        }

        $loanState = Loan::query()
            ->where('member_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['requested', 'borrowed', 'late'])
            ->orderByRaw("CASE WHEN status IN ('borrowed', 'late') THEN 0 ELSE 1 END")
            ->value('status');

        if ($loanState === 'borrowed' || $loanState === 'late') {
            return 'borrowed';
        }

        if ($loanState === 'requested') {
            return 'requested';
        }

        return $stockAvailable > 0 ? 'available' : 'unavailable';
    }
}
