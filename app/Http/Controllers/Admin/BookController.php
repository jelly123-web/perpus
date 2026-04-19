<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Book;
use App\Models\BookProcurement;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    use HandlesAsyncRequests;

    private const MIN_PUBLISHED_YEAR = 1901;

    public function index(): View
    {
        $books = Book::query()->with('category')->latest()->paginate(10);
        $categories = Category::query()->orderBy('name')->get();
        $procurementSuggestions = BookProcurement::query()
            ->with(['category', 'proposer.role'])
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
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

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
            'category_id' => $this->nullableNumber($request->input('category_id')),
            'notes' => $this->nullableText($request->input('notes')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'digits:4', 'between:'.self::MIN_PUBLISHED_YEAR.','.((int) now()->addYear()->format('Y'))],
            'category_id' => ['nullable', 'exists:categories,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $procurement = BookProcurement::query()->create([
            ...$data,
            'status' => 'pending',
            'proposed_by' => $request->user()?->id,
        ]);

        ActivityLogger::log('book_procurements', 'create', 'Mengusulkan pengadaan buku '.$procurement->title, [
            'procurement_id' => $procurement->id,
        ]);

        return $this->successResponse($request, 'Usulan pengadaan buku berhasil dikirim.');
    }

    public function approveProcurement(Request $request, BookProcurement $procurement): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()?->role?->name === 'kepsek' || $request->user()?->isSuperAdmin(), 403);
        abort_if($procurement->status !== 'pending', 422, 'Usulan ini sudah diproses.');

        $book = Book::query()
            ->when(
                filled($procurement->isbn),
                fn ($query) => $query->where('isbn', $procurement->isbn),
                fn ($query) => $query->where('title', $procurement->title)->where('author', $procurement->author)
            )
            ->first();

        if ($book) {
            $book->increment('stock_total', $procurement->quantity);
            $book->increment('stock_available', $procurement->quantity);
        } else {
            $book = Book::query()->create([
                'title' => $procurement->title,
                'author' => $procurement->author,
                'isbn' => $procurement->isbn,
                'publisher' => $procurement->publisher,
                'published_year' => $procurement->published_year,
                'category_id' => $procurement->category_id,
                'stock_total' => $procurement->quantity,
                'stock_available' => $procurement->quantity,
                'description' => $procurement->notes,
            ]);
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
}
