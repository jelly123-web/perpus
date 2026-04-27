<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Sanction;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LoanRequestController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user?->role?->name, ['siswa', 'guru'], true), 403);

        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'borrowed_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:borrowed_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = Book::query()
            ->withCount([
                'loans as requested_loans_count' => fn ($query) => $query->where('status', 'requested'),
            ])
            ->findOrFail($data['book_id']);
        $today = Carbon::today()->toDateString();
        $requestableStock = max(0, (int) $book->stock_available - (int) ($book->requested_loans_count ?? 0));

        if ($requestableStock < 1) {
            $message = 'Stok buku sedang habis. Pilih buku lain atau hubungi petugas.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['loan_request' => $message]);
        }

        $activeBorrowingBan = Sanction::query()
            ->where('member_id', $user->id)
            ->where('type', 'suspend_borrowing')
            ->where('status', 'active')
            ->where(function ($query) use ($today): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $today);
            })
            ->latest('starts_at')
            ->first();

        if ($activeBorrowingBan) {
            $until = optional($activeBorrowingBan->ends_at)->translatedFormat('d M Y') ?? 'waktu yang belum ditentukan';
            $message = 'Akun Anda masih disanksi dan belum bisa mengajukan pinjam sampai '.$until.'.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['loan_request' => $message]);
        }

        $hasOpenRequest = Loan::query()
            ->where('member_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['requested', 'borrowed', 'late'])
            ->exists();

        if ($hasOpenRequest) {
            $message = 'Buku ini sudah ada di daftar pengajuan Anda atau masih sedang dipinjam di akun ini.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['loan_request' => $message]);
        }

        $loan = Loan::query()->create([
            'book_id' => $book->id,
            'member_id' => $user->id,
            'processed_by' => null,
            'borrowed_at' => Carbon::parse($data['borrowed_at'])->toDateString(),
            'due_at' => Carbon::parse($data['borrowed_at'])->addDay()->toDateString(),
            'status' => 'requested',
            'notes' => $data['notes'] ?? 'Pengajuan dibuat lewat akun peminjam.',
        ]);

        ActivityLogger::log(
            'loans',
            'create',
            'Mengajukan peminjaman buku '.$book->title,
            ['loan_id' => $loan->id, 'source' => 'member_dashboard']
        );

        $successMessage = 'Pengajuan pinjam berhasil dikirim. Batas waktu pinjam otomatis 1 hari dan petugas akan memprosesnya.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'loan_id' => $loan->id,
            ]);
        }

        return back()->with('status', $successMessage);
    }
}
