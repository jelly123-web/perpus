<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAsyncRequests;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Sanction;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LoanController extends Controller
{
    use HandlesAsyncRequests;

    public function index(): View
    {
        $today = Carbon::today();
        $loans = Loan::query()->with(['book', 'member', 'processor'])->latest()->paginate(10);
        $requestedLoans = $this->getRequestedLoans();
        $sanctionableLoans = Loan::query()
            ->with(['book', 'member'])
            ->latest()
            ->take(100)
            ->get();
        $sanctions = Sanction::query()
            ->with(['loan.book', 'member', 'processor'])
            ->latest()
            ->take(10)
            ->get();
        $sanctionMonitoring = Sanction::query()
            ->with(['member', 'loan.book'])
            ->where('type', 'suspend_borrowing')
            ->latest('starts_at')
            ->take(20)
            ->get()
            ->map(function (Sanction $sanction) use ($today): Sanction {
                $isCompleted = $sanction->status === 'completed';
                $isExpired = ! $isCompleted && $sanction->ends_at && $sanction->ends_at->lt($today);

                $sanction->monitoring_state = $isCompleted ? 'completed' : ($isExpired ? 'expired' : 'active');

                return $sanction;
            });
        $activeLoans = Loan::query()
            ->with(['book', 'member'])
            ->whereIn('status', ['borrowed', 'late'])
            ->latest('borrowed_at')
            ->get();
        $books = Book::query()
            ->where('stock_available', '>', 0)
            ->where('status', 'available')
            ->orderBy('title')
            ->get();
        $members = User::query()->whereHas('role', fn ($query) => $query->whereIn('name', ['siswa', 'guru']))->orderBy('name')->get();
        $memberStatuses = $members->map(function (User $member) use ($today): User {
            $activeSanction = Sanction::query()
                ->where('member_id', $member->id)
                ->where('type', 'suspend_borrowing')
                ->where('status', 'active')
                ->where(function ($query) use ($today): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', $today->toDateString());
                })
                ->latest('starts_at')
                ->first();

            $member->borrower_status = $activeSanction ? 'sanctioned' : 'active';
            $member->active_sanction = $activeSanction;

            return $member;
        });
        $loanStats = [
            'total' => Loan::query()->count(),
            'requested' => Loan::query()->where('status', 'requested')->count(),
            'borrowed' => Loan::query()->where('status', 'borrowed')->count(),
            'returned' => Loan::query()->where('status', 'returned')->count(),
            'late' => Loan::query()->where('status', 'late')->count(),
        ];

        return view('admin.loans.index', compact('loans', 'requestedLoans', 'sanctionableLoans', 'sanctions', 'sanctionMonitoring', 'activeLoans', 'books', 'members', 'memberStatuses', 'loanStats'));
    }

    public function liveSnapshot(): JsonResponse
    {
        return response()->json([
            'signature' => $this->loanLiveSignature(),
        ]);
    }

    public function requestedPanel(): JsonResponse
    {
        $requestedLoans = $this->getRequestedLoans();

        return response()->json([
            'signature' => $this->requestedPanelSignature(),
            'requested_count' => $requestedLoans->count(),
            'html' => view('admin.loans._requested-panel', compact('requestedLoans'))->render(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'member_id' => ['nullable', 'exists:users,id'],
            'borrower_name' => ['required_without:member_id', 'nullable', 'string', 'max:255'],
            'borrowed_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:borrowed_at'],
            'notes' => ['nullable', 'string'],
        ], [
            'borrower_name.required_without' => 'Nama peminjam wajib diisi jika tidak memilih anggota.',
        ]);

        $book = Book::query()->findOrFail($data['book_id']);

        // Only check for sanctions if a member is selected
        if (!empty($data['member_id'])) {
            $activeBorrowingBan = Sanction::query()
                ->where('member_id', $data['member_id'])
                ->where('type', 'suspend_borrowing')
                ->where('status', 'active')
                ->where(function ($query) use ($data): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', $data['borrowed_at']);
                })
                ->latest('starts_at')
                ->first();

            if ($activeBorrowingBan) {
                $until = optional($activeBorrowingBan->ends_at)->translatedFormat('d M Y') ?? 'waktu yang belum ditentukan';

                return $this->errorResponse($request, 'Peminjam masih terkena sanksi tidak boleh meminjam sampai '.$until.'.', 422, 'loan');
            }
        }

        if ($book->stock_available < 1) {
            return $this->errorResponse($request, 'Stok buku tidak tersedia.', 422, 'loan');
        }

        $borrowedAt = Carbon::parse($data['borrowed_at'])->toDateString();
        $dueAt = Carbon::parse($data['due_at'])->toDateString();

        $loan = Loan::query()->create([
            ...$data,
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'processed_by' => $request->user()->id,
            'status' => 'borrowed',
        ]);

        $book->decrement('stock_available');
        ActivityLogger::log('loans', 'create', 'Mencatat peminjaman buku '.$book->title, ['loan_id' => $loan->id]);

        return $this->successResponse($request, 'Peminjaman berhasil ditambahkan secara manual.');
    }

    public function storeSanction(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'type' => ['required', 'in:suspend_borrowing,warning,replace_book'],
            'reason' => ['required', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $loan = Loan::query()->with('member')->findOrFail($data['loan_id']);

        if (!$loan->member_id) {
            return $this->errorResponse($request, 'Sanksi hanya dapat diberikan kepada anggota terdaftar.', 422, 'loan');
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $durationDays = isset($data['duration_days']) ? (int) $data['duration_days'] : null;
        $endsAt = $data['type'] === 'suspend_borrowing' && $durationDays !== null
            ? $startsAt->copy()->addDays($durationDays)->toDateString()
            : null;

        $sanction = Sanction::query()->create([
            'loan_id' => $loan->id,
            'member_id' => $loan->member_id,
            'processed_by' => $request->user()->id,
            'type' => $data['type'],
            'status' => 'active',
            'reason' => $data['reason'],
            'duration_days' => $durationDays,
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $endsAt,
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLogger::log(
            'sanctions',
            'create',
            'Menambahkan sanksi '.$sanction->type.' untuk '.$loan->member?->name,
            ['sanction_id' => $sanction->id, 'loan_id' => $loan->id]
        );

        return $this->successResponse($request, 'Sanksi berhasil dicatat.');
    }

    public function updateBorrowerStatus(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:active,sanctioned'],
            'reason' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'starts_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $member = User::query()->findOrFail($data['member_id']);

        if ($data['status'] === 'active') {
            Sanction::query()
                ->where('member_id', $member->id)
                ->where('type', 'suspend_borrowing')
                ->where('status', 'active')
                ->update([
                    'status' => 'completed',
                    'ends_at' => Carbon::today()->toDateString(),
                ]);

            ActivityLogger::log('sanctions', 'update', 'Mengaktifkan kembali status peminjam '.$member->name, ['member_id' => $member->id]);

            return $this->successResponse($request, 'Status peminjam diperbarui menjadi aktif.');
        }

        $startsAt = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : Carbon::today();
        $durationDays = isset($data['duration_days']) ? (int) $data['duration_days'] : null;

        $sanction = Sanction::query()->create([
            'loan_id' => null,
            'member_id' => $member->id,
            'processed_by' => $request->user()->id,
            'type' => 'suspend_borrowing',
            'status' => 'active',
            'reason' => $data['reason'] ?: 'Status peminjam diubah menjadi disanksi oleh petugas.',
            'duration_days' => $durationDays,
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $durationDays !== null ? $startsAt->copy()->addDays($durationDays)->toDateString() : null,
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLogger::log('sanctions', 'create', 'Menandai peminjam '.$member->name.' sebagai disanksi', ['sanction_id' => $sanction->id, 'member_id' => $member->id]);

        return $this->successResponse($request, 'Status peminjam diperbarui menjadi disanksi.');
    }

    private function getRequestedLoans(): Collection
    {
        return Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'requested')
            ->latest('created_at')
            ->take(20)
            ->get();
    }

    private function requestedPanelSignature(): string
    {
        $requestedLoans = Loan::query()
            ->where('status', 'requested')
            ->latest('created_at')
            ->take(20)
            ->get(['id', 'status', 'created_at', 'updated_at']);

        return sha1(json_encode([
            'requested_count' => Loan::query()->where('status', 'requested')->count(),
            'requested_loans' => $requestedLoans
                ->map(fn (Loan $loan) => [
                    'id' => $loan->id,
                    'status' => $loan->status,
                    'created_at' => optional($loan->created_at)?->format('Y-m-d H:i:s.u'),
                    'updated_at' => optional($loan->updated_at)?->format('Y-m-d H:i:s.u'),
                ])
                ->values()
                ->all(),
        ], JSON_UNESCAPED_UNICODE));
    }

    private function loanLiveSignature(): string
    {
        $recentLoans = Loan::query()
            ->latest('updated_at')
            ->take(30)
            ->get(['id', 'status', 'member_id', 'book_id', 'updated_at', 'returned_at']);
        $activeSanctions = Sanction::query()
            ->where('status', 'active')
            ->latest('updated_at')
            ->take(20)
            ->get(['id', 'member_id', 'type', 'status', 'starts_at', 'ends_at', 'updated_at']);

        return sha1(json_encode([
            'loan_count' => Loan::query()->count(),
            'requested_count' => Loan::query()->where('status', 'requested')->count(),
            'borrowing_count' => Loan::query()->whereIn('status', ['borrowed', 'late'])->count(),
            'active_sanction_count' => Sanction::query()->where('status', 'active')->count(),
            'recent_loans' => $recentLoans
                ->map(fn (Loan $loan) => [
                    'id' => $loan->id,
                    'status' => $loan->status,
                    'member_id' => $loan->member_id,
                    'book_id' => $loan->book_id,
                    'updated_at' => optional($loan->updated_at)?->format('Y-m-d H:i:s.u'),
                    'returned_at' => optional($loan->returned_at)?->toDateString(),
                ])
                ->values()
                ->all(),
            'active_sanctions' => $activeSanctions
                ->map(fn (Sanction $sanction) => [
                    'id' => $sanction->id,
                    'member_id' => $sanction->member_id,
                    'type' => $sanction->type,
                    'status' => $sanction->status,
                    'starts_at' => optional($sanction->starts_at)?->toDateString(),
                    'ends_at' => optional($sanction->ends_at)?->toDateString(),
                    'updated_at' => optional($sanction->updated_at)?->format('Y-m-d H:i:s.u'),
                ])
                ->values()
                ->all(),
        ], JSON_UNESCAPED_UNICODE));
    }

    public function updateSanctionStatus(Request $request, Sanction $sanction): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,completed'],
        ]);

        $sanction->update([
            'status' => $data['status'],
            'ends_at' => $data['status'] === 'completed'
                ? ($sanction->ends_at?->toDateString() ?? Carbon::today()->toDateString())
                : $sanction->ends_at?->toDateString(),
        ]);

        ActivityLogger::log('sanctions', 'update', 'Memperbarui status sanksi #'.$sanction->id.' menjadi '.$data['status'], ['sanction_id' => $sanction->id]);

        return $this->successResponse($request, 'Status sanksi berhasil diperbarui.');
    }

    public function returnBook(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'exists:loans,id'],
            'returned_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $loan = Loan::query()
            ->with('book')
            ->whereIn('status', ['borrowed', 'late'])
            ->findOrFail($data['loan_id']);

        $returnedAt = Carbon::parse($data['returned_at']);
        $isLate = $loan->due_at && $returnedAt->greaterThan($loan->due_at);
        $daysLate = $isLate ? max($loan->due_at->diffInDays($returnedAt), 1) : 0;

        $loan->update([
            'status' => 'returned',
            'returned_at' => $returnedAt->toDateString(),
            'notes' => $data['notes'] ?? $loan->notes,
            'fine_amount' => 0,
        ]);

        $loan->book?->increment('stock_available');
  
        if ($isLate && $loan->member_id) {
            Sanction::query()->create([
                'loan_id' => $loan->id,
                'member_id' => $loan->member_id,
                'processed_by' => $request->user()->id,
                'type' => 'suspend_borrowing',
                'status' => 'active',
                'reason' => 'Terlambat mengembalikan buku '.$daysLate.' hari.',
                'duration_days' => $daysLate,
                'starts_at' => $returnedAt->toDateString(),
                'ends_at' => $returnedAt->copy()->addDays($daysLate)->toDateString(),
                'notes' => 'Sanksi otomatis karena pengembalian melewati batas waktu.',
            ]);
  
            ActivityLogger::log(
                'sanctions',
                'create',
                'Memberikan sanksi otomatis untuk keterlambatan pengembalian #'.$loan->id,
                ['loan_id' => $loan->id, 'member_id' => $loan->member_id, 'days_late' => $daysLate]
            );
        }

        ActivityLogger::log(
            'loans',
            'update',
            'Mencatat pengembalian buku '.($loan->book?->title ?? '#'.$loan->id).($isLate ? ' dengan status terlambat' : ''),
            ['loan_id' => $loan->id, 'is_late' => $isLate]
        );

        return $this->successResponse($request, $isLate ? 'Pengembalian dicatat. Buku ini terlambat dikembalikan dan sanksi otomatis sudah diberikan.' : 'Pengembalian berhasil dicatat. Buku kembali tepat waktu.');
    }

    public function update(Request $request, Loan $loan): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:requested,borrowed,returned,late'],
            'returned_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $previousStatus = $loan->status;
        $nextStatus = $data['status'];
        $stockHoldingStatuses = ['borrowed', 'late'];
        $wasHoldingStock = in_array($previousStatus, $stockHoldingStatuses, true);
        $willHoldStock = in_array($nextStatus, $stockHoldingStatuses, true);

        if (! $wasHoldingStock && $willHoldStock) {
            if (($loan->book?->stock_available ?? 0) < 1) {
                return $this->errorResponse($request, 'Stok buku tidak tersedia untuk menyetujui pengajuan ini.', 422, 'loan');
            }

            $loan->book?->decrement('stock_available');
        }

        $loan->update([
            'status' => $nextStatus,
            'processed_by' => $request->user()->id,
            'returned_at' => $nextStatus === 'returned'
                ? ($data['returned_at'] ?? Carbon::today()->toDateString())
                : null,
            'fine_amount' => 0,
            'notes' => $data['notes'] ?? $loan->notes,
        ]);

        if ($wasHoldingStock && ! $willHoldStock) {
            $loan->book()->increment('stock_available');
        }

        ActivityLogger::log('loans', 'update', 'Mengubah status peminjaman #'.$loan->id.' dari '.$previousStatus.' ke '.$nextStatus, ['loan_id' => $loan->id]);

        return $this->successResponse($request, 'Data peminjaman berhasil diperbarui.');
    }
}
