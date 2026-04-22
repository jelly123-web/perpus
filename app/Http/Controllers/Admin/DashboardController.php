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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function chatbotRespond(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,bot'],
            'history.*.text' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $message = trim((string) $data['message']);
        $lower = mb_strtolower($message);
        $user = $request->user();
        $history = collect($data['history'] ?? [])
            ->map(fn (array $item): array => [
                'role' => $item['role'],
                'text' => trim((string) $item['text']),
            ])
            ->filter(fn (array $item): bool => $item['text'] !== '')
            ->take(-12)
            ->values()
            ->all();
        $apiKey = config('services.gemini.key');
        $aiConfigured = $apiKey && $apiKey !== 'your_gemini_api_key_here';

        if ($aiConfigured) {
            try {
                $aiResponse = $this->getGeminiResponse($message, $history, $user);
                if ($aiResponse) {
                    return response()->json([
                        'status' => 'success',
                        'reply' => $aiResponse,
                        'source' => 'gemini',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Chatbot AI Error: '.$e->getMessage());

                return response()->json([
                    'status' => 'error',
                    'reply' => $e->getMessage(),
                    'source' => 'gemini',
                ], 503);
            }
        }

        $reply = 'Mode AI belum aktif. Isi `GEMINI_API_KEY` yang valid agar chatbot bisa menjawab pertanyaan umum, lalu coba lagi.';

        $contains = function (array $needles) use ($lower): bool {
            foreach ($needles as $needle) {
                if ($needle !== '' && str_contains($lower, $needle)) {
                    return true;
                }
            }
            return false;
        };

        if ($contains(['halo', 'hai', 'hello', 'assalamualaikum', 'permisi'])) {
            $reply = 'Halo! Saya chatbot perpustakaan. Saya bisa bantu: cari buku, aturan pinjam, aturan kembali, dan status pinjaman kamu.';
        } elseif ($contains(['cara pinjam', 'pinjam buku', 'ajukan pinjam', 'peminjaman'])) {
            $reply = "Cara pinjam:\n1) Cari buku di dashboard peminjam.\n2) Klik buku yang dipilih.\n3) Isi tanggal (otomatis 1 hari) lalu klik Ajukan Pinjam.\n4) Tunggu petugas memproses pengajuan.";
        } elseif ($contains(['cara kembali', 'pengembalian', 'kembaliin', 'kembalikan'])) {
            $reply = "Cara pengembalian:\n1) Serahkan buku ke petugas.\n2) Petugas input pengembalian di menu Peminjaman Buku.\n3) Kalau lewat jatuh tempo, status jadi terlambat dan bisa kena sanksi.";
        } elseif ($contains(['batas', 'durasi', 'berapa hari', 'jatuh tempo'])) {
            $reply = 'Batas waktu peminjaman adalah 1 hari dari tanggal pinjam. Pastikan dikembalikan sebelum jatuh tempo untuk menghindari sanksi.';
        } elseif ($contains(['sanksi', 'kena sanksi', 'denda', 'hukuman'])) {
            $reply = 'Jika terlambat, akun bisa dikenai sanksi (misalnya suspend pinjam beberapa hari, warning, atau ganti buku). Status sanksi bisa dilihat di dashboard peminjam.';
        } elseif ($contains(['laporan', 'report', 'cetak'])) {
            $reply = 'Untuk laporan, buka menu Laporan. Di sana ada tombol Cetak, Unduh Excel, dan Unduh PDF.';
        } elseif ($contains(['status saya', 'status pinjam', 'pinjaman saya', 'riwayat'])) {
            if ($user && $user->hasPermission('view_borrower_history')) {
                $active = Loan::query()->where('member_id', $user->id)->whereIn('status', ['borrowed', 'late'])->count();
                $requested = Loan::query()->where('member_id', $user->id)->where('status', 'requested')->count();
                $returned = Loan::query()->where('member_id', $user->id)->where('status', 'returned')->count();
                $reply = "Status kamu:\n- Pengajuan: {$requested}\n- Sedang dipinjam: {$active}\n- Riwayat selesai: {$returned}";
            } else {
                $reply = 'Fitur status pinjaman tersedia untuk akun peminjam.';
            }
        } elseif ($contains(['cari', 'cari buku', 'buku', 'judul'])) {
            $keyword = '';
            if (preg_match('/cari\s+buku\s+(.+)$/i', $message, $matches)) {
                $keyword = trim($matches[1]);
            } elseif (preg_match('/buku\s+(.+)$/i', $message, $matches)) {
                $keyword = trim($matches[1]);
            } elseif (preg_match('/cari\s+(.+)$/i', $message, $matches)) {
                $keyword = trim($matches[1]);
            }

            if ($keyword === '' || mb_strlen($keyword) < 2) {
                $reply = 'Tulis seperti: "cari buku matematika" atau "cari novel".';
            } else {
                $books = Book::query()
                    ->where('title', 'like', '%'.$keyword.'%')
                    ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$keyword.'%'])
                    ->orderBy('title')
                    ->take(5)
                    ->get();

                if ($books->isEmpty()) {
                    $reply = 'Buku dengan kata kunci "'.$keyword.'" belum ditemukan.';
                } else {
                    $lines = $books->map(function (Book $book): string {
                        return '- '.$book->title.' (stok '.$book->stock_available.')';
                    })->implode("\n");
                    $reply = "Hasil pencarian \"{$keyword}\":\n{$lines}";
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'reply' => $reply,
            'source' => 'fallback',
        ]);
    }

    private function getGeminiResponse(string $message, array $history, ?User $user): ?string
    {
        $apiKey = config('services.gemini.key');
        $preferredModel = (string) config('services.gemini.model', 'gemma-3-1b-it');

        if (!$apiKey || $apiKey === 'your_gemini_api_key_here') {
            return null;
        }

        $context = $this->getLibraryContext($user);
        $instructionText = implode("\n\n", [
            'Anda adalah asisten AI percakapan umum di aplikasi perpustakaan sekolah bernama Perpus Pintar.',
            'Jawab seperti asisten chat modern: natural, langsung, membantu, dan bisa membahas topik umum apa pun, bukan hanya aturan pinjam buku.',
            'Jika pertanyaan menyentuh data perpustakaan atau akun, gunakan konteks yang tersedia dan jangan mengarang data.',
            'Jika pengguna meminta opini, penjelasan, ide, rangkuman, bantuan belajar, coding, atau pengetahuan umum, jawab secara normal seperti chatbot AI umum.',
            'Jika jawaban berkaitan dengan fitur aplikasi perpustakaan, sesuaikan dengan konteks berikut:',
            $context,
        ]);

        $contents = collect($history)
            ->map(function (array $item): array {
                return [
                    'role' => $item['role'] === 'bot' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $item['text']],
                    ],
                ];
            })
            ->push([
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                ],
            ])
            ->values()
            ->all();

        $models = collect([$preferredModel, 'gemma-3-1b-it', 'gemma-3-4b-it', 'gemini-2.0-flash', 'gemini-flash-lite-latest'])
            ->filter()
            ->unique()
            ->values();
        $response = null;
        $lastStatus = null;

        foreach ($models as $model) {
            $usesGemma = str_starts_with($model, 'gemma-');
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            $payload = [
                'contents' => $usesGemma
                    ? array_merge(
                        [[
                            'role' => 'user',
                            'parts' => [[
                                'text' => $instructionText,
                            ]],
                        ]],
                        $contents
                    )
                    : $contents,
                'generationConfig' => [
                    'temperature' => 0.8,
                    'topP' => 0.95,
                    'maxOutputTokens' => 900,
                ],
            ];

            if (! $usesGemma) {
                $payload['systemInstruction'] = [
                    'parts' => [
                        [
                            'text' => $instructionText,
                        ],
                    ],
                ];
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $apiKey,
            ])->timeout(45)->post($apiUrl, $payload);

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
                if (is_string($text) && trim($text) !== '') {
                    return trim($text);
                }
            }

            $lastStatus = $response->status();

            Log::warning('Gemini API request failed.', [
                'model' => $model,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($lastStatus === 429) {
                break;
            }

            if (!in_array($lastStatus, [404, 500, 503], true)) {
                break;
            }
        }

        $status = $lastStatus ?? ($response?->status() ?? 0);

        if ($status === 429) {
            throw new \RuntimeException('AI Google terhubung, tetapi quota atau rate limit API key Gemini sedang habis. Tunggu limit reset atau pakai API key/proyek Google AI lain yang masih punya quota.');
        }

        if ($status === 404) {
            throw new \RuntimeException('Model Gemini tidak ditemukan untuk API key ini. Coba ganti `GEMINI_MODEL` ke model yang tersedia, misalnya `gemini-2.0-flash`.');
        }

        throw new \RuntimeException('AI tidak bisa dihubungi saat ini. Periksa model Gemini, API key, quota, atau koneksi server ke Google AI.');
    }

    private function getLibraryContext(?User $user): string
    {
        $context = "";
        if ($user) {
            $context .= "- Nama Pengguna: {$user->name}\n";
            $context .= "- Role: " . ($user->role?->name ?? 'User') . "\n";

            if ($user->hasPermission('view_borrower_history')) {
                $active = Loan::query()->where('member_id', $user->id)->whereIn('status', ['borrowed', 'late'])->count();
                $requested = Loan::query()->where('member_id', $user->id)->where('status', 'requested')->count();
                $returned = Loan::query()->where('member_id', $user->id)->where('status', 'returned')->count();
                $context .= "- Status Pinjaman: {$requested} pengajuan, {$active} dipinjam, {$returned} selesai.\n";
            }
        } else {
            $context .= "- Pengguna belum login (Guest).\n";
        }

        $totalBooks = Book::query()->count();
        $context .= "- Total koleksi buku di perpustakaan: {$totalBooks} judul.\n";

        return $context;
    }

    public function index(Request $request)
    {
        $today = now();
        $user = auth()->user();
        $isBorrowerDashboard = in_array($user?->role?->name, ['siswa', 'guru'], true);
        $isPrincipalDashboard = $user?->role?->name === 'kepsek';
        $isSuperAdminDashboard = (bool) $user?->isSuperAdmin();
        $canViewActivityLog = (bool) ($user?->isSuperAdmin() || $user?->hasPermission('manage_roles') || $user?->hasPermission('manage_users'));
        $canManageLoans = (bool) $user?->hasPermission('manage_loans');
        $bookFilters = $this->getBorrowerBookFilters($request);

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

        $superAdminProcurementUpdates = $isSuperAdminDashboard
            ? BookProcurement::query()
                ->with(['category', 'proposer.role', 'approver', 'rejector'])
                ->whereIn('status', ['approved', 'rejected'])
                ->orderByRaw('COALESCE(rejected_at, approved_at, updated_at) DESC')
                ->take(6)
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
            'isSuperAdminDashboard',
            'superAdminProcurementUpdates',
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
        $isPrincipal = (bool) ($user?->role?->name === 'kepsek' || $user?->isSuperAdmin());

        abort_unless($isBorrower || $isAdminOrOfficer || $isPrincipal, 403);

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

        if ($user?->role?->name === 'kepsek') {
            $pendingProcurements = BookProcurement::query()
                ->with(['category', 'proposer'])
                ->where('status', 'pending')
                ->latest('created_at')
                ->take(10)
                ->get();

            $notifications = $pendingProcurements
                ->map(fn (BookProcurement $procurement) => [
                    'tone' => 'info',
                    'icon' => 'clipboard-plus',
                    'title' => 'Usulan pengadaan baru',
                    'body' => ($procurement->proposer?->name ?? 'Petugas').' mengusulkan buku "'.($procurement->title ?? 'Buku').'"'
                        .($procurement->category?->name ? ' kategori '.$procurement->category->name : '')
                        .' sebanyak '.((int) $procurement->quantity).' buku.',
                    'signature' => 'principal-procurement-'.$procurement->id.'-'.$procurement->status,
                    'href' => route('dashboard'),
                ])
                ->values();

            return response()->json([
                'notifications' => $notifications,
            ]);
        }

        if ($user?->isSuperAdmin()) {
            $procurementUpdates = BookProcurement::query()
                ->with(['category', 'proposer', 'approver', 'rejector'])
                ->whereIn('status', ['approved', 'rejected'])
                ->orderByRaw('COALESCE(rejected_at, approved_at, updated_at) DESC')
                ->take(10)
                ->get();

            $notifications = $procurementUpdates
                ->map(function (BookProcurement $procurement): array {
                    $isRejected = $procurement->status === 'rejected';
                    $decisionMaker = $isRejected
                        ? ($procurement->rejector?->name ?? 'Pemeriksa')
                        : ($procurement->approver?->name ?? 'Pemeriksa');

                    return [
                        'tone' => $isRejected ? 'danger' : 'success',
                        'icon' => $isRejected ? 'circle-x' : 'badge-check',
                        'title' => $isRejected ? 'Usulan pengadaan ditolak' : 'Usulan pengadaan disetujui',
                        'body' => 'Usulan buku "'.($procurement->title ?? 'Buku').'" dari '.($procurement->proposer?->name ?? 'Petugas')
                            .' telah '.($isRejected ? 'ditolak' : 'disetujui')
                            .' oleh '.$decisionMaker.'.',
                        'signature' => 'superadmin-procurement-'.$procurement->id.'-'.$procurement->status.'-'.optional($isRejected ? $procurement->rejected_at : $procurement->approved_at)?->timestamp,
                        'href' => route('admin.books.index'),
                    ];
                })
                ->values();

            return response()->json([
                'notifications' => $notifications,
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
                'icon' => 'triangle-alert',
                'title' => 'Buku terlambat dikembalikan',
                'body' => ($loan->member?->name ?? 'Anggota').' terlambat mengembalikan buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => 'admin-late-'.$loan->id.'-'.$loan->status,
                'href' => route('admin.loans.index'),
            ]))
            ->merge($requestedLoans->map(fn (Loan $loan) => [
                'tone' => 'info',
                'icon' => 'info',
                'title' => 'Permintaan pinjam baru',
                'body' => ($loan->member?->name ?? 'Anggota').' mengajukan pinjam buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => 'admin-requested-'.$loan->id,
                'href' => route('admin.loans.index'),
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
