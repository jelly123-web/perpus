<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookProcurement;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now();
        $user = auth()->user();
        $isBorrowerDashboard = in_array($user?->role?->name, ['siswa', 'guru'], true);
        $isPrincipalDashboard = $user?->role?->name === 'kepsek';
        $canViewActivityLog = (bool) ($user?->isSuperAdmin() || $user?->hasPermission('manage_roles') || $user?->hasPermission('manage_users'));
        $canManageLoans = (bool) $user?->hasPermission('manage_loans');
        $bookFilters = $this->getBorrowerBookFilters($request);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Daftar buku diperbarui.',
                'status' => 'success'
            ]);
        }

        $stats = [
            'books' => Book::query()->count(),
            'borrowed' => Loan::query()->whereIn('status', ['borrowed', 'late'])->count(),
            'members' => User::query()->whereHas('role', fn ($query) => $query->whereIn('name', ['siswa', 'guru']))->count(),
            'late' => Loan::query()->where('status', 'late')->count(),
        ];

        $popularBooks = Book::query()
            ->with('category')
            ->withCount('loans')
            ->having('loans_count', '>', 0)
            ->orderByDesc('loans_count')
            ->take(4)
            ->get();
        $borrowerCategories = $this->getBorrowerCategories();
        $borrowerBooks = $this->getBorrowerBooks($bookFilters);

        $borrowerActiveSanction = $isBorrowerDashboard
            ? $this->getActiveBorrowerSanction($user?->id, $today)
            : null;

        $borrowerLoans = $isBorrowerDashboard
            ? Loan::query()
                ->with(['book.category', 'processor'])
                ->where('member_id', $user?->id)
                ->latest('created_at')
                ->take(8)
                ->get()
            : collect();

        $borrowerLoanStats = [
            'requested' => $isBorrowerDashboard ? $borrowerLoans->where('status', 'requested')->count() : 0,
            'borrowed' => $isBorrowerDashboard ? $borrowerLoans->whereIn('status', ['borrowed', 'late'])->count() : 0,
            'returned' => $isBorrowerDashboard ? $borrowerLoans->where('status', 'returned')->count() : 0,
        ];

        $borrowerGuideSteps = $isBorrowerDashboard
            ? [
                ['number' => '1', 'title' => 'Cari Buku', 'description' => 'Pilih buku yang tersedia dari daftar katalog lalu ajukan pinjam lewat akun Anda.'],
                ['number' => '2', 'title' => 'Tunggu Proses Petugas', 'description' => 'Petugas akan mengecek stok dan memproses pengajuan sebelum buku bisa diambil.'],
                ['number' => '3', 'title' => 'Mengembalikan Buku', 'description' => 'Kembalikan buku langsung ke petugas sebelum batas pengembalian. Jika terlambat, akun peminjam akan kena sanksi.'],
                ['number' => '4', 'title' => 'Menerima Sanksi (Kalau Ada)', 'description' => 'Kalau telat, Anda tidak bisa pinjam sementara, mendapat peringatan, dan harus menunggu sampai sanksi selesai baru bisa pinjam lagi.'],
                ['number' => '5', 'title' => 'Lihat Riwayat', 'description' => 'Buka halaman riwayat peminjaman untuk cek buku yang sedang dipinjam dan pantau status akun, apakah aktif atau sedang kena sanksi.'],
                ['number' => '6', 'title' => 'Notifikasi (Kalau Ada Sistemnya)', 'description' => 'Dapat pengingat tanggal pengembalian, sanksi, dan status buku langsung dari akun peminjam.'],
            ]
            : [];

        $borrowerNotifications = $isBorrowerDashboard
            ? $this->buildBorrowerNotifications($borrowerLoans, $borrowerActiveSanction, $today)
            : collect();

        $recentLoans = Loan::query()
            ->with(['book', 'member'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = $canViewActivityLog
            ? ActivityLog::query()
                ->with(['user.role'])
                ->when(
                    $user?->isSuperAdmin(),
                    fn ($query) => $query->whereHas('user.role', fn ($roleQuery) => $roleQuery->where('name', 'super_admin'))
                )
                ->whereIn('action', ['create', 'update', 'delete'])
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $principalActivityLogs = $isPrincipalDashboard
            ? ActivityLog::query()
                ->with(['user.role'])
                ->whereHas('user.role', fn ($query) => $query->where('name', 'petugas'))
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $principalMetrics = $isPrincipalDashboard
            ? [
                'petugas_active_today' => ActivityLog::query()
                    ->whereHas('user.role', fn ($query) => $query->where('name', 'petugas'))
                    ->whereDate('created_at', $today->toDateString())
                    ->distinct('user_id')
                    ->count('user_id'),
                'petugas_actions_today' => ActivityLog::query()
                    ->whereHas('user.role', fn ($query) => $query->where('name', 'petugas'))
                    ->whereDate('created_at', $today->toDateString())
                    ->count(),
                'books_growth' => Book::query()->whereDate('created_at', '>=', $today->copy()->subDays(30)->toDateString())->count(),
                'loans_growth' => Loan::query()->whereDate('created_at', '>=', $today->copy()->subDays(30)->toDateString())->count(),
                'service_score' => max(0, 100 - ((int) Loan::query()->where('status', 'late')->count() * 5) - ((int) Loan::query()->where('status', 'requested')->count() * 2)),
                'pending_requests' => Loan::query()->where('status', 'requested')->count(),
                'late_loans' => Loan::query()->where('status', 'late')->count(),
                'returned_today' => Loan::query()->whereDate('returned_at', $today->toDateString())->count(),
                'pending_procurements' => BookProcurement::query()->where('status', 'pending')->count(),
            ]
            : [];

        $principalProcurements = $isPrincipalDashboard
            ? BookProcurement::query()
                ->with(['category', 'proposer.role'])
                ->where('status', 'pending')
                ->latest()
                ->take(10)
                ->get()
            : collect();

        $categoryPercentages = Category::query()
            ->withCount('books')
            ->get()
            ->map(function (Category $category): array {
                $totalBooks = max(Book::query()->count(), 1);

                return [
                    'name' => $category->name,
                    'percentage' => (int) round(($category->books_count / $totalBooks) * 100),
                ];
            })
            ->sortByDesc('percentage')
            ->take(5)
            ->values();

        $loanChartLabels = collect(range(6, 0))
            ->map(fn (int $offset) => $today->copy()->subDays($offset)->translatedFormat('D'))
            ->values();

        $loanChartBorrowed = collect(range(6, 0))
            ->map(fn (int $offset) => Loan::query()
                ->whereDate('borrowed_at', $today->copy()->subDays($offset)->toDateString())
                ->count())
            ->values();

        $loanChartReturned = collect(range(6, 0))
            ->map(fn (int $offset) => Loan::query()
                ->whereDate('returned_at', $today->copy()->subDays($offset)->toDateString())
                ->count())
            ->values();

        $categoryChart = Category::query()
            ->withCount('books')
            ->orderByDesc('books_count')
            ->take(6)
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'total' => $category->books_count,
            ])
            ->values();

        $totalBooks = max((int) $stats['books'], 1);
        $borrowedBooks = (int) $stats['borrowed'];
        $lateBooks = (int) $stats['late'];
        $availableBookCount = max($totalBooks - $borrowedBooks, 0);
        $availablePercentage = max(min((int) round(($availableBookCount / $totalBooks) * 100), 100), 0);
        $borrowedPercentage = max(min((int) round(($borrowedBooks / $totalBooks) * 100), 100), 0);
        $latePercentage = max(min((int) round(($lateBooks / $totalBooks) * 100), 100), 0);

        $dashboardMeta = [
            'today_label' => $today->translatedFormat('l, d F Y'),
            'ring_circumference' => 263.9,
            'ring_offset' => round(263.9 - (($availablePercentage / 100) * 263.9), 1),
            'available_books' => $availableBookCount,
            'available_percentage' => $availablePercentage,
            'borrowed_books' => $borrowedBooks,
            'borrowed_percentage' => $borrowedPercentage,
            'late_books' => $lateBooks,
            'late_percentage' => $latePercentage,
            'top_categories' => $categoryPercentages->take(4)->values(),
            'book_palette' => [
                ['from' => 'from-sage-500', 'to' => 'to-sage-800', 'soft' => 'bg-sage-50 text-sage-700', 'bar' => 'from-sage-500 to-sage-400'],
                ['from' => 'from-cream-400', 'to' => 'to-cream-700', 'soft' => 'bg-cream-100 text-cream-800', 'bar' => 'from-cream-500 to-cream-400'],
                ['from' => 'from-terra-400', 'to' => 'to-terra-700', 'soft' => 'bg-terra-50 text-terra-700', 'bar' => 'from-terra-500 to-terra-400'],
                ['from' => 'from-slate2-500', 'to' => 'to-slate2-800', 'soft' => 'bg-slate2-100 text-slate2-700', 'bar' => 'from-slate2-500 to-slate2-400'],
                ['from' => 'from-sage-300', 'to' => 'to-sage-600', 'soft' => 'bg-sage-100 text-sage-800', 'bar' => 'from-sage-400 to-sage-300'],
            ],
        ];

        $loanChart = [
            'labels' => $loanChartLabels,
            'borrowed' => $loanChartBorrowed,
            'returned' => $loanChartReturned,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'popularBooks',
            'borrowerBooks',
            'borrowerCategories',
            'bookFilters',
            'recentLoans',
            'recentActivities',
            'isPrincipalDashboard',
            'principalActivityLogs',
            'principalMetrics',
            'principalProcurements',
            'isBorrowerDashboard',
            'canViewActivityLog',
            'canManageLoans',
            'categoryPercentages',
            'categoryChart',
            'dashboardMeta',
            'loanChart',
            'borrowerLoanStats',
            'borrowerActiveSanction',
            'borrowerGuideSteps',
            'borrowerNotifications',
        ));
    }

    public function history(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('view_borrower_history'), 403);

        $today = now();
        $borrowerActiveSanction = $this->getActiveBorrowerSanction($user?->id, $today);

        $borrowerLoans = Loan::query()
            ->with(['book.category', 'processor'])
            ->where('member_id', $user?->id)
            ->latest('created_at')
            ->paginate(10);

        $borrowerHistoryStats = [
            'active_loans' => Loan::query()->where('member_id', $user?->id)->whereIn('status', ['borrowed', 'late'])->count(),
            'requested' => Loan::query()->where('member_id', $user?->id)->where('status', 'requested')->count(),
            'returned' => Loan::query()->where('member_id', $user?->id)->where('status', 'returned')->count(),
            'account_status' => $borrowerActiveSanction ? 'Kena sanksi' : 'Aktif',
        ];

        return view('member.history', compact('borrowerLoans', 'borrowerHistoryStats', 'borrowerActiveSanction'));
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $isBorrower = (bool) $user?->hasPermission('view_borrower_history');
        $isAdminOrOfficer = (bool) $user?->hasPermission('manage_loans');

        abort_unless($isBorrower || $isAdminOrOfficer, 403);

        $today = now();

        if ($isBorrower) {
            $borrowerActiveSanction = $this->getActiveBorrowerSanction($user?->id, $today);
            $borrowerLoans = Loan::query()
                ->with(['book.category', 'processor'])
                ->where('member_id', $user?->id)
                ->latest('created_at')
                ->take(10)
                ->get();

            $notifications = $this->buildBorrowerNotifications($borrowerLoans, $borrowerActiveSanction, $today);

            return response()->json([
                'notifications' => $notifications,
                'account_status' => $borrowerActiveSanction ? 'Sedang kena sanksi' : 'Aktif',
                'sanction_message' => $borrowerActiveSanction
                    ? 'Akun Anda sedang disanksi dan belum bisa mengajukan pinjam.'
                    : null,
                'borrower_loan_stats' => [
                    'requested' => $borrowerLoans->where('status', 'requested')->count(),
                    'borrowed' => $borrowerLoans->whereIn('status', ['borrowed', 'late'])->count(),
                    'returned' => $borrowerLoans->where('status', 'returned')->count(),
                ],
            ]);
        }

        // Admin or Officer Notifications
        $lateLoans = Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'late')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $requestedLoans = Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'requested')
            ->latest('created_at')
            ->take(5)
            ->get();

        $notifications = collect()
            ->merge($lateLoans->map(fn (Loan $loan) => [
                'tone' => 'danger',
                'title' => 'Buku terlambat dikembalikan',
                'body' => ($loan->member?->name ?? 'Anggota').' terlambat mengembalikan buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => 'admin-late-'.$loan->id.'-'.$loan->status,
            ]))
            ->merge($requestedLoans->map(fn (Loan $loan) => [
                'tone' => 'info',
                'title' => 'Permintaan pinjam baru',
                'body' => ($loan->member?->name ?? 'Anggota').' mengajukan pinjam buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => 'admin-requested-'.$loan->id,
            ]))
            ->take(10)
            ->values();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function borrowerBooks(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('view_borrower_history'), 403);

        $bookFilters = $this->getBorrowerBookFilters($request);
        $borrowerActiveSanction = $this->getActiveBorrowerSanction($user?->id, now());
        $categories = $this->getBorrowerCategories();
        $books = $this->getBorrowerBooks($bookFilters);

        return response()->json([
            'filters' => $bookFilters,
            'categories' => $categories->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
            ])->values(),
            'books' => $books->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author ?? 'Penulis tidak tersedia',
                'category' => $book->category?->name ?? 'Tanpa kategori',
                'stock' => (int) $book->stock_available,
                'cover_url' => $book->cover_image ? asset('storage/'.$book->cover_image) : null,
                'borrowed_at' => now()->toDateString(),
                'due_at' => now()->addDay()->toDateString(),
                'borrow_state' => $borrowerActiveSanction ? 'sanctioned' : ($book->stock_available > 0 ? 'available' : 'unavailable'),
                'can_borrow' => $book->stock_available > 0 && ! $borrowerActiveSanction,
            ])->values(),
        ]);
    }

    private function getBorrowerBookFilters(Request $request): array
    {
        return [
            'keyword' => trim((string) $request->string('q')),
            'category' => (string) $request->string('category'),
            'availability' => (string) $request->string('availability', 'available'),
        ];
    }

    private function getBorrowerCategories()
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    private function getBorrowerBooks(array $bookFilters)
    {
        return Book::query()
            ->with('category')
            ->when(
                $bookFilters['keyword'] !== '',
                fn ($query) => $query->where(function ($innerQuery) use ($bookFilters): void {
                    $innerQuery
                        ->where('title', 'like', $bookFilters['keyword'].'%')
                        ->orWhere('author', 'like', $bookFilters['keyword'].'%')
                        ->orWhere('title', 'like', '%'.$bookFilters['keyword'].'%');
                })
            )
            ->when(
                $bookFilters['category'] !== '',
                fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $bookFilters['category']))
            )
            ->when(
                $bookFilters['availability'] === 'available',
                fn ($query) => $query->where('stock_available', '>', 0)
            )
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$bookFilters['keyword'].'%'])
            ->orderBy('title')
            ->take(12)
            ->get();
    }

    private function getActiveBorrowerSanction(?int $userId, \Illuminate\Support\Carbon $today): ?Sanction
    {
        if (! $userId) {
            return null;
        }

        return Sanction::query()
            ->where('member_id', $userId)
            ->where('type', 'suspend_borrowing')
            ->where('status', 'active')
            ->where(function ($query) use ($today): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $today->toDateString());
            })
            ->latest('starts_at')
            ->first();
    }

    private function buildBorrowerNotifications($borrowerLoans, ?Sanction $borrowerActiveSanction, \Illuminate\Support\Carbon $today)
    {
        return collect()
            ->when(
                $borrowerActiveSanction,
                fn ($notifications) => $notifications->push([
                    'tone' => 'danger',
                    'title' => 'Sanksi akun aktif',
                    'body' => $borrowerActiveSanction->ends_at
                        ? 'Akun Anda kena sanksi sampai '.$borrowerActiveSanction->ends_at->translatedFormat('d M Y').'.'
                        : 'Akun Anda sedang kena sanksi sampai ada pemberitahuan berikutnya.',
                    'signature' => 'sanction-active-'.($borrowerActiveSanction->id ?? 'current'),
                ])
            )
            ->merge(
                $borrowerLoans
                    ->whereIn('status', ['borrowed', 'late'])
                    ->take(3)
                    ->map(function (Loan $loan) use ($today): array {
                        $isLate = $loan->status === 'late' || ($loan->due_at && $loan->due_at->lt($today));

                        return [
                            'tone' => $isLate ? 'danger' : 'info',
                            'title' => $isLate ? 'Buku melewati batas pengembalian' : 'Pengingat tanggal pengembalian',
                            'body' => ($loan->book?->title ?? 'Buku').' harus dikembalikan paling lambat '.(optional($loan->due_at)->translatedFormat('d M Y') ?? '-').'.',
                            'signature' => 'loan-due-'.$loan->id.'-'.$loan->status,
                        ];
                    })
            )
            ->merge(
                $borrowerLoans
                    ->where('status', 'requested')
                    ->take(2)
                    ->map(fn (Loan $loan): array => [
                        'tone' => 'success',
                        'title' => 'Status buku menunggu petugas',
                        'body' => 'Pengajuan untuk buku '.($loan->book?->title ?? 'pilihan Anda').' sedang menunggu diproses petugas.',
                        'signature' => 'loan-requested-'.$loan->id,
                    ])
            )
            ->take(4)
            ->values();
    }
}
