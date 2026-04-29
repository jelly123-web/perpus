<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Book;
use App\Models\BookProcurement;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    use HandlesAsyncRequests;

    private const MIN_PUBLISHED_YEAR = 1901;

    public function index(): View
    {
        $books = Book::query()->with('category')->latest()->paginate(10);
        $categories = Category::query()->orderBy('name')->get();
        $procurementSuggestions = BookProcurement::query()
            ->with(['category', 'proposer.role', 'approver', 'rejector'])
            ->latest()
            ->take(8)
            ->get();
        $bookStats = [
            'total' => Book::query()->count(),
            'stock_total' => Book::query()->sum('stock_total'),
            'stock_available' => Book::query()->sum('stock_available'),
            'categories' => Category::query()->count(),
        ];

        return view('admin.books.index', compact('books', 'categories', 'bookStats', 'procurementSuggestions'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge([
            'isbn' => $this->nullableText($request->input('isbn')),
            'category_id' => $this->nullableNumber($request->input('category_id')),
            'publisher' => $this->nullableText($request->input('publisher')),
            'place_of_publication' => $this->nullableText($request->input('place_of_publication')),
            'published_year' => $this->nullableNumber($request->input('published_year')),
            'description' => $this->nullableText($request->input('description')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:255', 'unique:books,isbn'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'place_of_publication' => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'digits:4', 'between:'.self::MIN_PUBLISHED_YEAR.','.((int) now()->addYear()->format('Y'))],
            'stock_total' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:available,damaged,lost,hidden'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('book-covers', 'public');
        }

        $data['stock_available'] = $data['stock_total'];
        $book = Book::query()->create($data);

        ActivityLogger::log('books', 'create', 'Menambahkan buku '.$book->title, ['book_id' => $book->id]);

        return $this->successResponse($request, 'Buku berhasil ditambahkan.');
    }

    public function import(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($data['import_file']->getRealPath(), 'r');

        if ($handle === false) {
            return $this->errorResponse($request, 'File import tidak bisa dibaca.', 422, 'import_file');
        }

        $headers = fgetcsv($handle) ?: [];
        $normalizedHeaders = collect($headers)
            ->map(fn ($header) => Str::lower(trim((string) $header)))
            ->values()
            ->all();

        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $rowData = $this->mapCsvRow($normalizedHeaders, $row);
                $title = $this->nullableText($rowData['title'] ?? null);
                $author = $this->nullableText($rowData['author'] ?? null);

                if (! filled($title) || ! filled($author)) {
                    continue;
                }

                $isbn = $this->nullableText($rowData['isbn'] ?? null);
                $categoryId = $this->resolveBookCategoryId($rowData['category'] ?? null);
                $publishedYear = $this->sanitizePublishedYear($rowData['published_year'] ?? null);
                $stockTotal = $this->sanitizeNonNegativeInteger($rowData['stock_total'] ?? null);
                $stockAvailable = array_key_exists('stock_available', $rowData)
                    ? $this->sanitizeNonNegativeInteger($rowData['stock_available'] ?? null)
                    : $stockTotal;

                $book = Book::withTrashed()
                    ->when(
                        filled($isbn),
                        fn ($query) => $query->where('isbn', $isbn),
                        fn ($query) => $query->where('title', $title)->where('author', $author)
                    )
                    ->first();

                if ($book && method_exists($book, 'trashed') && $book->trashed()) {
                    $book->restore();
                }

                $payload = [
                    'title' => $title,
                    'author' => $author,
                    'isbn' => $isbn,
                    'category_id' => $categoryId,
                    'publisher' => $this->nullableText($rowData['publisher'] ?? null),
                    'place_of_publication' => $this->nullableText($rowData['place_of_publication'] ?? null),
                    'published_year' => $publishedYear,
                    'stock_total' => $stockTotal,
                    'stock_available' => min($stockAvailable, $stockTotal),
                    'description' => $this->nullableText($rowData['description'] ?? null),
                ];

                if ($book) {
                    $book->update($payload);
                } else {
                    Book::query()->create($payload);
                }

                $imported++;
            } catch (\Throwable) {
                continue;
            }
        }

        fclose($handle);

        ActivityLogger::log('books', 'create', 'Import data buku via CSV', ['imported_count' => $imported]);

        return $this->successResponse($request, $imported > 0 ? "Import buku berhasil. {$imported} data diproses." : 'Import selesai, tidak ada data buku yang valid.');
    }

    public function export(): StreamedResponse
    {
        $fileName = 'backup-books-'.now()->format('Ymd-His').'.csv';
        $columns = ['title', 'author', 'isbn', 'category', 'publisher', 'place_of_publication', 'published_year', 'stock_total', 'stock_available', 'description'];

        return response()->streamDownload(function () use ($columns): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            Book::query()
                ->with('category')
                ->orderBy('title')
                ->chunk(200, function ($books) use ($handle): void {
                    foreach ($books as $book) {
                        fputcsv($handle, [
                            $book->title,
                            $book->author,
                            $book->isbn,
                            $book->category?->name,
                            $book->publisher,
                            $book->place_of_publication,
                            $book->published_year,
                            $book->stock_total,
                            $book->stock_available,
                            $book->description,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function update(Request $request, Book $book): JsonResponse|RedirectResponse
    {
        $request->merge([
            'isbn' => $this->nullableText($request->input('isbn')),
            'category_id' => $this->nullableNumber($request->input('category_id')),
            'publisher' => $this->nullableText($request->input('publisher')),
            'place_of_publication' => $this->nullableText($request->input('place_of_publication')),
            'published_year' => $this->nullableNumber($request->input('published_year')),
            'description' => $this->nullableText($request->input('description')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:255', 'unique:books,isbn,'.$book->id],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'place_of_publication' => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'digits:4', 'between:'.self::MIN_PUBLISHED_YEAR.','.((int) now()->addYear()->format('Y'))],
            'stock_total' => ['required', 'integer', 'min:0'],
            'stock_available' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:available,damaged,lost,hidden'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')->store('book-covers', 'public');
        }

        $book->update($data);
        ActivityLogger::log('books', 'update', 'Mengubah buku '.$book->title, ['book_id' => $book->id]);

        return $this->successResponse($request, 'Buku berhasil diperbarui.');
    }

    public function destroy(Request $request, Book $book): JsonResponse|RedirectResponse
    {
        ActivityLogger::log('books', 'delete', 'Menghapus buku '.$book->title, ['book_id' => $book->id]);
        $book->delete();

        return $this->successResponse($request, 'Buku berhasil dihapus.');
    }

    public function storeProcurement(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge([
            'isbn' => $this->nullableText($request->input('isbn')),
            'publisher' => $this->nullableText($request->input('publisher')),
            'published_year' => $this->nullableNumber($request->input('published_year')),
            'category_name' => $this->nullableText($request->input('category_name')),
            'notes' => $this->nullableText($request->input('notes')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'digits:4', 'between:'.self::MIN_PUBLISHED_YEAR.','.((int) now()->addYear()->format('Y'))],
            'category_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $categoryId = $this->resolveProcurementCategoryId($data['category_name'] ?? null);

        $procurement = BookProcurement::query()->create([
            ...$data,
            'category_id' => $categoryId,
            'status' => 'pending',
            'proposed_by' => $request->user()?->id,
        ]);

        ActivityLogger::log('book_procurements', 'create', 'Mengusulkan pengadaan buku '.$procurement->title, [
            'procurement_id' => $procurement->id,
        ]);

        return $this->successResponse($request, 'Usulan pengadaan buku berhasil dikirim ke kepsek.');
    }

    public function approveProcurement(Request $request, BookProcurement $procurement): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()?->role?->name === 'kepsek' || $request->user()?->isSuperAdmin(), 403);
        abort_if($procurement->status !== 'pending', 422, 'Usulan ini sudah diproses.');

        $normalizedIsbn = $this->nullableText($procurement->isbn);

        $book = Book::withTrashed()
            ->when(
                filled($normalizedIsbn),
                fn ($query) => $query->where('isbn', $normalizedIsbn),
                fn ($query) => $query->where('title', $procurement->title)->where('author', $procurement->author)
            )
            ->first();

        if ($book && method_exists($book, 'trashed') && $book->trashed()) {
            $book->restore();
        }

        if ($book) {
            $book->increment('stock_total', $procurement->quantity);
            $book->syncStockAvailability();
        } else {
            try {
                $book = Book::query()->create([
                    'title' => $procurement->title,
                    'author' => $procurement->author,
                    'isbn' => $normalizedIsbn,
                    'publisher' => $procurement->publisher,
                    'published_year' => $procurement->published_year,
                    'category_id' => $procurement->category_id,
                    'stock_total' => $procurement->quantity,
                    'stock_available' => $procurement->quantity,
                    'description' => $procurement->notes,
                ]);
            } catch (QueryException $exception) {
                $book = Book::withTrashed()
                    ->when(
                        filled($normalizedIsbn),
                        fn ($query) => $query->where('isbn', $normalizedIsbn),
                        fn ($query) => $query->where('title', $procurement->title)->where('author', $procurement->author)
                    )
                    ->first();

                if (! $book) {
                    throw $exception;
                }

                if (method_exists($book, 'trashed') && $book->trashed()) {
                    $book->restore();
                }

                $book->increment('stock_total', $procurement->quantity);
                $book->syncStockAvailability();
            }
        }

        $procurement->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
        ]);

        ActivityLogger::log('book_procurements', 'update', 'Menyetujui pengadaan buku '.$procurement->title, [
            'procurement_id' => $procurement->id,
            'book_id' => $book->id,
        ]);

        return $this->successResponse($request, 'Usulan pengadaan disetujui dan koleksi buku diperbarui.');
    }

    public function rejectProcurement(Request $request, BookProcurement $procurement): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()?->role?->name === 'kepsek' || $request->user()?->isSuperAdmin(), 403);
        abort_if($procurement->status !== 'pending', 422, 'Usulan ini sudah diproses.');

        $procurement->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()?->id,
            'rejected_at' => now(),
        ]);

        ActivityLogger::log('book_procurements', 'update', 'Menolak pengadaan buku '.$procurement->title, [
            'procurement_id' => $procurement->id,
        ]);

        return $this->successResponse($request, 'Usulan pengadaan berhasil ditolak.');
    }

    private function nullableText(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableNumber(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function resolveProcurementCategoryId(?string $categoryName): ?int
    {
        if (! filled($categoryName)) {
            return null;
        }

        $category = Category::withTrashed()
            ->whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])
            ->first();

        if ($category) {
            if (method_exists($category, 'trashed') && $category->trashed()) {
                $category->restore();
            }

            return $category->id;
        }

        $baseSlug = Str::slug($categoryName);
        $slug = $baseSlug !== '' ? $baseSlug : 'kategori';
        $suffix = 1;

        while (Category::withTrashed()->where('slug', $slug)->exists()) {
            $slug = ($baseSlug !== '' ? $baseSlug : 'kategori').'-'.$suffix;
            $suffix++;
        }

        return Category::query()->create([
            'name' => $categoryName,
            'slug' => $slug,
        ])->id;
    }

    private function resolveBookCategoryId(?string $categoryName): ?int
    {
        return $this->resolveProcurementCategoryId($this->nullableText($categoryName));
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $row
     * @return array<string, mixed>
     */
    private function mapCsvRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    private function sanitizePublishedYear(mixed $value): ?int
    {
        $normalized = $this->nullableText($value);

        if (! filled($normalized) || ! preg_match('/^\d{4}$/', $normalized)) {
            return null;
        }

        $year = (int) $normalized;
        $maxYear = (int) now()->addYear()->format('Y');

        return ($year >= self::MIN_PUBLISHED_YEAR && $year <= $maxYear) ? $year : null;
    }

    private function sanitizeNonNegativeInteger(mixed $value): int
    {
        $normalized = $this->nullableText($value);

        if (! filled($normalized) || ! preg_match('/^-?\d+$/', $normalized)) {
            return 0;
        }

        return max((int) $normalized, 0);
    }
}
