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
            ->take(-6)
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
            }
        }

        $reply = $this->buildFallbackChatbotReply($message, $lower, $user, $aiConfigured);

        return response()->json([
            'status' => 'success',
            'reply' => $reply,
            'source' => 'fallback',
        ]);
    }

    private function buildFallbackChatbotReply(string $message, string $lower, ?User $user, bool $aiConfigured): string
    {
        $reply = $aiConfigured
            ? 'Saya tetap bisa bantu di mode cepat. Coba tulis pertanyaanmu langsung, misalnya minta penjelasan, hitungan sederhana, ide, caption, pesan singkat, coding dasar, atau soal perpustakaan.'
            : 'Saya sedang jalan di mode lokal. Coba tulis pertanyaanmu langsung, misalnya minta penjelasan, hitungan sederhana, ide, caption, pesan singkat, coding dasar, atau soal perpustakaan.';

        if ($mathReply = $this->trySolveMathExpression($message)) {
            return $mathReply;
        }

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
        } elseif ($contains(['siapa kamu', 'kamu siapa'])) {
            $reply = 'Saya ChatBot Perpus. Kalau AI online tersedia saya bisa jawab lebih luas, tapi saat mode lokal saya tetap bisa bantu soal perpustakaan, hitungan sederhana, ide singkat, caption, waktu, dan tanggal.';
        } elseif ($contains(['bisa apa', 'apa yang bisa kamu lakukan', 'fitur kamu'])) {
            $reply = "Saya bisa bantu:\n- cari buku\n- jelaskan aturan pinjam/kembali\n- cek status pinjaman\n- hitung sederhana\n- buat caption singkat\n- kasih ide cepat\nKalau Gemini aktif, jawaban saya juga bisa lebih umum seperti AI biasa.";
        } elseif ($contains(['jam berapa', 'sekarang jam berapa', 'waktu sekarang'])) {
            $reply = 'Sekarang jam '.now()->translatedFormat('H:i').' WIB/ICT.';
        } elseif ($contains(['hari apa', 'tanggal berapa', 'tanggal sekarang'])) {
            $reply = 'Sekarang '.now()->translatedFormat('l, d F Y').'.';
        } elseif ($contains(['apa itu ', 'jelaskan ', 'pengertian '])) {
            $reply = $this->buildDefinitionReply($message);
        } elseif ($contains(['buat caption', 'bikin caption', 'caption untuk'])) {
            $reply = $this->buildCaptionReply($message);
        } elseif ($contains(['beri ide', 'kasih ide', 'butuh ide', 'ide untuk'])) {
            $reply = $this->buildIdeasReply($message);
        } elseif ($contains(['buat surat', 'bikin surat', 'buat pesan', 'bikin pesan'])) {
            $reply = $this->buildMessageReply($message);
        } elseif ($contains(['buat kode', 'bikin kode', 'coding', 'program', 'php', 'javascript', 'python'])) {
            $reply = $this->buildCodingReply($lower);
        } elseif ($contains(['belajar', 'cara belajar', 'tips belajar'])) {
            $reply = "Tips belajar cepat:\n- pecah materi jadi bagian kecil\n- fokus 25-30 menit lalu istirahat\n- coba jelaskan ulang dengan kata sendiri\n- latihan soal atau contoh nyata\n- ulang lagi bagian yang masih lemah";
        } elseif ($contains(['ringkas', 'rangkum', 'summary'])) {
            $reply = 'Bisa. Kirim teks yang ingin diringkas, lalu saya bantu buat versi singkatnya. Kalau AI online aktif, hasilnya akan lebih fleksibel.';
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

        return $reply;
    }

    private function trySolveMathExpression(string $message): ?string
    {
        $expression = mb_strtolower(trim($message));
        $expression = preg_replace('/^(berapa|hitung|hasil|tolong hitung)\s+/i', '', $expression);
        $expression = str_replace(['x', ':', '='], ['*', '/', ''], $expression);

        if (! preg_match('/[0-9]/', $expression)) {
            return null;
        }

        if (! preg_match('/^[0-9\.\+\-\*\/%\(\)\s]+$/', $expression)) {
            return null;
        }

        if (str_contains($expression, '**') || str_contains($expression, '//')) {
            return null;
        }

        try {
            /** @var mixed $result */
            $result = eval('return '.$expression.';');

            if (! is_numeric($result)) {
                return null;
            }

            $formatted = floor((float) $result) == (float) $result
                ? (string) (int) $result
                : rtrim(rtrim(number_format((float) $result, 6, '.', ''), '0'), '.');

            return 'Hasilnya: '.$formatted;
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildCaptionReply(string $message): string
    {
        $topic = $this->extractTopic($message, ['buat caption', 'bikin caption', 'caption untuk']);
        $topicText = $topic !== '' ? $topic : 'postingan kamu';

        return "Coba salah satu caption ini:\n"
            ."- {$topicText} yang sederhana tapi tetap berkesan.\n"
            ."- Cerita kecil dari {$topicText}, semoga bikin hari lebih baik.\n"
            ."- {$topicText} hari ini, semoga bermanfaat dan menginspirasi.";
    }

    private function buildIdeasReply(string $message): string
    {
        $topic = $this->extractTopic($message, ['beri ide', 'kasih ide', 'butuh ide', 'ide untuk']);
        $topicText = $topic !== '' ? $topic : 'topik kamu';

        return "Ide cepat untuk {$topicText}:\n"
            ."- buat versi yang simpel dan mudah dijalankan dulu\n"
            ."- tambahkan unsur yang unik atau beda dari biasanya\n"
            ."- sesuaikan dengan siapa target yang akan melihat atau memakai\n"
            ."- buat 2-3 variasi lalu pilih yang paling jelas manfaatnya";
    }

    private function buildDefinitionReply(string $message): string
    {
        $topic = $this->extractTopic($message, ['apa itu', 'jelaskan', 'pengertian']);
        $topicText = $topic !== '' ? $topic : 'topik itu';

        return "{$topicText} adalah sesuatu yang perlu dilihat dari fungsi, tujuan, dan contohnya.\n"
            ."Kalau kamu mau, kirim topiknya lebih spesifik, misalnya: \"jelaskan fotosintesis\" atau \"apa itu variabel di pemrograman\", nanti saya jawab lebih pas.";
    }

    private function buildMessageReply(string $message): string
    {
        $topic = $this->extractTopic($message, ['buat surat', 'bikin surat', 'buat pesan', 'bikin pesan']);
        $topicText = $topic !== '' ? $topic : 'keperluan kamu';

        return "Contoh pesan singkat untuk {$topicText}:\n"
            ."Halo, izin menyampaikan bahwa saya membutuhkan bantuan terkait {$topicText}. Mohon informasinya jika ada langkah yang perlu saya ikuti. Terima kasih.";
    }

    private function buildCodingReply(string $lower): string
    {
        if (str_contains($lower, 'php')) {
            return "Contoh PHP sederhana:\n```php\n<?php\n\$nama = 'Dunia';\necho 'Halo, ' . \$nama;\n```\nKalau mau, bilang juga kamu butuh contoh PHP untuk apa.";
        }

        if (str_contains($lower, 'javascript')) {
            return "Contoh JavaScript sederhana:\n```javascript\nconst nama = 'Dunia';\nconsole.log(`Halo, ${nama}`);\n```\nKalau mau, saya bisa bantu contoh JS untuk form, array, atau fetch.";
        }

        if (str_contains($lower, 'python')) {
            return "Contoh Python sederhana:\n```python\nnama = 'Dunia'\nprint(f'Halo, {nama}')\n```\nKalau mau, saya bisa bantu contoh Python untuk perulangan, fungsi, atau input data.";
        }

        return "Bisa bantu coding dasar. Sebutkan bahasanya ya, misalnya:\n- PHP\n- JavaScript\n- Python\nLalu tulis kebutuhanmu, misalnya form login, loop, fungsi, atau hitung data.";
    }

    private function extractTopic(string $message, array $prefixes): string
    {
        $normalized = trim($message);

        foreach ($prefixes as $prefix) {
            if (preg_match('/'.preg_quote($prefix, '/').'\s+(.+)$/i', $normalized, $matches)) {
                return trim($matches[1], " \t\n\r\0\x0B.,!?");
            }
        }

        return '';
    }

    private function getGeminiResponse(string $message, array $history, ?User $user): ?string
    {
        $apiKey = config('services.gemini.key');
        $preferredModel = (string) config('services.gemini.model', 'gemini-2.0-flash');

        if (!$apiKey || $apiKey === 'your_gemini_api_key_here') {
            return null;
        }

        $context = $this->getLibraryContext($user);
        $instructionText = implode("\n", [
            'Anda adalah asisten AI cepat di aplikasi perpustakaan sekolah Perpus Pintar.',
            'Jawab natural, langsung, dan ringkas.',
            'Utamakan jawaban 1-4 kalimat kecuali pengguna meminta detail.',
            'Jika pertanyaan menyentuh data perpustakaan atau akun, pakai konteks yang tersedia dan jangan mengarang.',
            'Konteks aplikasi:',
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

        $models = collect([$preferredModel, 'gemini-flash-lite-latest', 'gemini-2.0-flash', 'gemma-3-1b-it', 'gemma-3-4b-it'])
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
                    'temperature' => 0.55,
                    'topP' => 0.9,
                    'maxOutputTokens' => 320,
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
            ])->connectTimeout(3)->timeout(12)->post($apiUrl, $payload);

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
        $borrowerSnapshot = $isBorrowerDashboard
            ? $this->getBorrowerDashboardSnapshot($user?->id, $today)
            : null;
        $borrowerActiveSanction = $borrowerSnapshot['active_sanction'] ?? null;

        $borrowerCategories = $this->getBorrowerCategories();
        $borrowerBooks = $this->getBorrowerBooks($bookFilters);
        $borrowerOpenLoanStates = $isBorrowerDashboard
            ? $this->getBorrowerOpenLoanStates($user?->id)
            : collect();

        $borrowerBooks = $borrowerBooks->map(function (Book $book) use ($borrowerActiveSanction, $borrowerOpenLoanStates): Book {
            $loanState = $borrowerOpenLoanStates->get((int) $book->id);
            $requestableStock = max(0, (int) $book->stock_available - (int) ($book->requested_loans_count ?? 0));
            $borrowState = $borrowerActiveSanction
                ? 'sanctioned'
                : ($loanState ?: ($requestableStock > 0 ? 'available' : 'unavailable'));

            $book->borrow_state = $borrowState;
            $book->can_borrow = $borrowState === 'available';

            return $book;
        });

        $borrowerLoans = $borrowerSnapshot['recent_loans'] ?? collect();
        $borrowerLoanStats = $borrowerSnapshot['stats'] ?? [
            'requested' => 0,
            'borrowed' => 0,
            'returned' => 0,
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

        $borrowerNotifications = $borrowerSnapshot['notifications'] ?? collect();

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
            'borrowerSnapshot',
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
            $borrowerSnapshot = $this->getBorrowerDashboardSnapshot($user?->id, $today);

            return response()->json([
                'notifications' => $borrowerSnapshot['notifications'],
                'account_status' => $borrowerSnapshot['account_status'],
                'sanction_message' => $borrowerSnapshot['active_sanction']
                    ? 'Akun Anda sedang disanksi dan belum bisa mengajukan pinjam.'
                    : null,
                'borrower_loan_stats' => $borrowerSnapshot['stats'],
                'signature' => $borrowerSnapshot['signature'],
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

            if ($user->hasPermission('manage_loans')) {
                $notifications = $notifications
                    ->merge($this->buildLoanStatusNotifications('superadmin'))
                    ->sortByDesc(fn (array $notification) => str_contains((string) ($notification['signature'] ?? ''), 'superadmin-procurement-') ? 0 : 1)
                    ->take(10)
                    ->values();
            }

            return response()->json([
                'notifications' => $notifications,
            ]);
        }

        // Admin or Officer Notifications
        $notifications = $this->buildLoanStatusNotifications('admin');

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
        $borrowerOpenLoanStates = $this->getBorrowerOpenLoanStates($user?->id);

        return response()->json([
            'filters' => $bookFilters,
            'categories' => $categories->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
            ])->values(),
            'books' => $books->map(function (Book $book) use ($borrowerActiveSanction, $borrowerOpenLoanStates): array {
                $loanState = $borrowerOpenLoanStates->get((int) $book->id);
                $requestableStock = max(0, (int) $book->stock_available - (int) ($book->requested_loans_count ?? 0));
                $borrowState = $borrowerActiveSanction
                    ? 'sanctioned'
                    : ($loanState ?: ($requestableStock > 0 ? 'available' : 'unavailable'));

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author ?? 'Penulis tidak tersedia',
                    'category' => $book->category?->name ?? 'Tanpa kategori',
                    'stock' => (int) $book->stock_available,
                    'cover_url' => $book->cover_image ? asset('storage/'.$book->cover_image) : null,
                    'borrowed_at' => now()->toDateString(),
                    'due_at' => now()->addDay()->toDateString(),
                    'borrow_state' => $borrowState,
                    'can_borrow' => $borrowState === 'available',
                ];
            })->values(),
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
            ->withCount([
                'loans as requested_loans_count' => fn ($query) => $query->where('status', 'requested'),
            ])
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
                fn ($query) => $query->whereRaw(
                    'stock_available > (select count(*) from loans where loans.book_id = books.id and loans.status = ?)',
                    ['requested']
                )
            )
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$bookFilters['keyword'].'%'])
            ->orderBy('title')
            ->take(12)
            ->get();
    }

    private function getBorrowerOpenLoanStates(?int $userId)
    {
        if (! $userId) {
            return collect();
        }

        return Loan::query()
            ->select(['book_id', 'status'])
            ->where('member_id', $userId)
            ->whereIn('status', ['requested', 'borrowed', 'late'])
            ->orderByRaw("CASE WHEN status IN ('borrowed', 'late') THEN 0 ELSE 1 END")
            ->get()
            ->reduce(function ($carry, Loan $loan) {
                $bookId = (int) $loan->book_id;

                if (! $carry->has($bookId)) {
                    $carry->put($bookId, in_array($loan->status, ['borrowed', 'late'], true) ? 'borrowed' : 'requested');
                }

                return $carry;
            }, collect());
    }

    private function getBorrowerDashboardSnapshot(?int $userId, \Illuminate\Support\Carbon $today): array
    {
        if (! $userId) {
            return [
                'active_sanction' => null,
                'recent_loans' => collect(),
                'stats' => [
                    'requested' => 0,
                    'borrowed' => 0,
                    'returned' => 0,
                ],
                'account_status' => 'Aktif',
                'notifications' => collect(),
                'signature' => '0|0|0|0|0|0',
            ];
        }

        $activeSanction = $this->getActiveBorrowerSanction($userId, $today);
        $recentLoans = Loan::query()
            ->with(['book.category', 'processor'])
            ->where('member_id', $userId)
            ->latest('created_at')
            ->take(10)
            ->get();

        $requestedCount = Loan::query()
            ->where('member_id', $userId)
            ->where('status', 'requested')
            ->count();
        $borrowedCount = Loan::query()
            ->where('member_id', $userId)
            ->whereIn('status', ['borrowed', 'late'])
            ->count();
        $returnedCount = Loan::query()
            ->where('member_id', $userId)
            ->where('status', 'returned')
            ->count();

        $notifications = $this->buildBorrowerNotifications($recentLoans, $activeSanction, $today);

        return [
            'active_sanction' => $activeSanction,
            'recent_loans' => $recentLoans,
            'stats' => [
                'requested' => $requestedCount,
                'borrowed' => $borrowedCount,
                'returned' => $returnedCount,
            ],
            'account_status' => $activeSanction ? 'Sedang kena sanksi' : 'Aktif',
            'notifications' => $notifications,
            'signature' => $this->buildBorrowerSnapshotSignature(
                $recentLoans,
                $activeSanction,
                $requestedCount,
                $borrowedCount,
                $returnedCount,
                $notifications
            ),
        ];
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
                    'signature' => 'sanction-active-'.$this->buildSanctionSnapshotSignature($borrowerActiveSanction),
                ])
            )
            ->merge(
                $borrowerLoans
                    ->whereIn('status', ['borrowed', 'late'])
                    ->take(3)
                    ->map(function (Loan $loan): array {
                        $isLate = $loan->status === 'late';

                        return [
                            'tone' => $isLate ? 'danger' : 'info',
                            'title' => $isLate ? 'Buku melewati batas pengembalian' : 'Pengingat tanggal pengembalian',
                            'body' => ($loan->book?->title ?? 'Buku').' harus dikembalikan paling lambat '.(optional($loan->due_at)->translatedFormat('d M Y') ?? '-').'.',
                            'signature' => 'loan-due-'.$this->buildLoanSnapshotSignature($loan),
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
                        'signature' => 'loan-requested-'.$this->buildLoanSnapshotSignature($loan),
                    ])
            )
            ->take(4)
            ->values();
    }

    private function buildBorrowerSnapshotSignature($recentLoans, ?Sanction $activeSanction, int $requestedCount, int $borrowedCount, int $returnedCount, $notifications): string
    {
        return sha1(json_encode([
            'requested' => $requestedCount,
            'borrowed' => $borrowedCount,
            'returned' => $returnedCount,
            'active_sanction' => $activeSanction ? $this->buildSanctionSnapshotSignature($activeSanction) : null,
            'recent_loans' => $recentLoans
                ->take(10)
                ->map(fn (Loan $loan) => $this->buildLoanSnapshotSignature($loan))
                ->values()
                ->all(),
            'notifications' => collect($notifications)
                ->pluck('signature')
                ->filter()
                ->values()
                ->all(),
        ], JSON_UNESCAPED_UNICODE));
    }

    private function buildLoanSnapshotSignature(Loan $loan): string
    {
        return implode('|', [
            (string) $loan->id,
            (string) $loan->status,
            (string) optional($loan->borrowed_at)?->toDateString(),
            (string) optional($loan->due_at)?->toDateString(),
            (string) optional($loan->returned_at)?->toDateString(),
            (string) optional($loan->updated_at)?->format('Y-m-d H:i:s.u'),
        ]);
    }

    private function buildSanctionSnapshotSignature(Sanction $sanction): string
    {
        return implode('|', [
            (string) $sanction->id,
            (string) $sanction->status,
            (string) $sanction->type,
            (string) optional($sanction->starts_at)?->toDateString(),
            (string) optional($sanction->ends_at)?->toDateString(),
            (string) optional($sanction->updated_at)?->format('Y-m-d H:i:s.u'),
        ]);
    }

    private function buildLoanStatusNotifications(string $prefix)
    {
        $lateLoans = Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'late')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $borrowedLoans = Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'borrowed')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $requestedLoans = Loan::query()
            ->with(['book', 'member'])
            ->where('status', 'requested')
            ->latest('created_at')
            ->take(5)
            ->get();

        return collect()
            ->merge($lateLoans->map(fn (Loan $loan) => [
                'tone' => 'danger',
                'icon' => 'triangle-alert',
                'title' => 'Buku terlambat dikembalikan',
                'body' => ($loan->member?->name ?? 'Anggota').' terlambat mengembalikan buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => $prefix.'-late-'.$loan->id.'-'.optional($loan->updated_at)?->timestamp,
                'href' => route('admin.loans.index'),
                'timestamp' => optional($loan->updated_at)?->timestamp ?? 0,
            ]))
            ->merge($borrowedLoans->map(fn (Loan $loan) => [
                'tone' => 'success',
                'icon' => 'book-check',
                'title' => 'Buku berhasil dipinjam',
                'body' => ($loan->member?->name ?? 'Anggota').' sedang meminjam buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => $prefix.'-borrowed-'.$loan->id.'-'.optional($loan->updated_at)?->timestamp,
                'href' => route('admin.loans.index'),
                'timestamp' => optional($loan->updated_at)?->timestamp ?? 0,
            ]))
            ->merge($requestedLoans->map(fn (Loan $loan) => [
                'tone' => 'info',
                'icon' => 'info',
                'title' => 'Permintaan pinjam baru',
                'body' => ($loan->member?->name ?? 'Anggota').' mengajukan pinjam buku "'.($loan->book?->title ?? 'Buku').'".',
                'signature' => $prefix.'-requested-'.$loan->id.'-'.optional($loan->created_at)?->timestamp,
                'href' => route('admin.loans.index'),
                'timestamp' => optional($loan->created_at)?->timestamp ?? 0,
            ]))
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->map(function (array $notification): array {
                unset($notification['timestamp']);

                return $notification;
            })
            ->values();
    }
}
