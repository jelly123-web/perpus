@extends('layouts.admin')

@php($appName = \App\Models\Setting::valueOr('app_name', config('app.name', 'Laravel')))
@php($appColor = \App\Models\Setting::valueOr('app_color', '#FAFAFA'))

@section('content')
@php($title = 'Laporan')
@php($eyebrow = 'Analitik & Rekapitulasi')

<style>
    .report-page{display:flex;flex-direction:column;gap:32px;padding-bottom:60px;width:100%}
    .report-toolbar{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:10px 0 20px 0;border-bottom:1px solid var(--border-light)}
    .report-title{font-family:'Playfair Display',serif;font-size:36px;font-weight:800;letter-spacing:-.03em;color:var(--fg);margin:0}
    .report-actions{display:flex;gap:12px;flex-wrap:wrap}
    .btn-report-action, .btn-apply{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;border-radius:12px;background:#fff;border:1px solid var(--border-light);color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.25s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm);text-decoration:none;height:44px}
    .btn-report-action:hover, .btn-apply:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-1px);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-report-action:active, .btn-apply:active{transform:translateY(0);box-shadow:0 4px 10px var(--accent-glow)}
    
    .report-tabs{display:flex;gap:10px;padding:8px;background:var(--accent);border-radius:20px;width:fit-content}
    .report-tab{padding:12px 24px;border-radius:14px;font-size:14px;font-weight:700;color:rgba(255,255,255,0.8);text-decoration:none;transition:.3s ease;position:relative}
    .report-tab:hover{color:#fff;background:rgba(255,255,255,0.1)}
    .report-tab.active{background:#fff;color:var(--accent);box-shadow:0 8px 24px rgba(0,0,0,0.12);transform:scale(1.02)}
    
    .report-filter-card{background:#fff;border:1px solid var(--border-light);border-radius:28px;padding:32px;box-shadow:var(--shadow-sm)}
    .report-filter-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:24px;align-items:flex-end}
    
    .report-stats-container{display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:24px}
    .report-stat-card{background:#fff;border:1px solid var(--border-light);border-radius:28px;padding:28px;display:flex;flex-direction:column;gap:16px;box-shadow:var(--shadow-sm);transition:.3s ease;position:relative;overflow:hidden}
    .report-stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
    .report-stat-card::after{content:'';position:absolute;top:0;right:0;width:100px;height:100px;background:linear-gradient(135deg, transparent, rgba(var(--accent-rgb), 0.03));border-radius:0 0 0 100%}
    .report-stat-header{display:flex;align-items:center;justify-content:space-between}
    .report-stat-icon-box{width:64px;height:64px;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:24px}
    .report-stat-icon-box.books{background:var(--accent-glow);color:var(--accent)}
    .report-stat-icon-box.loans{background:var(--gold-glow);color:var(--gold)}
    .report-stat-icon-box.returns{background:var(--teal-glow);color:var(--teal)}
    .report-stat-content{display:flex;flex-direction:column}
    .report-stat-value{font-size:38px;font-weight:900;color:var(--fg);letter-spacing:-.02em}
    .report-stat-label{font-size:15px;font-weight:700;color:var(--muted);margin-top:2px}
    .report-stat-footer{padding-top:16px;border-top:1px solid var(--border-light);font-size:13px;color:var(--dim);display:flex;align-items:center;gap:6px}
    
    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px}
    .report-usage-widget{background:#fff;border:1px solid var(--border-light);border-radius:24px;padding:24px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:12px;min-height:130px;transition:.2s ease}
    .report-usage-widget:hover{background:var(--bg-soft)}
    .report-usage-tag{font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.12em}
    .report-usage-number{font-size:24px;font-weight:800;color:var(--fg)}
    .report-usage-desc{font-size:14px;color:var(--dim);font-weight:500}
    
    .report-section-card{background:#fff;border:1px solid var(--border-light);border-radius:32px;overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:20px;width:100%}
    .report-section-header{padding:32px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;gap:24px;background:linear-gradient(to right, var(--bg-soft), transparent)}
    .report-section-info{display:flex;flex-direction:column;gap:4px}
    .report-section-title-text{font-family:'Playfair Display',serif;font-size:28px;font-weight:800;color:var(--fg)}
    .report-section-subtitle-text{font-size:15px;color:var(--muted);max-width:500px;line-height:1.5}
    .report-section-count{padding:8px 18px;border-radius:12px;background:#fff;border:1px solid var(--border-light);color:var(--accent);font-size:13px;font-weight:800;box-shadow:var(--shadow-sm)}
    
    .report-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .report-table th{background:rgba(var(--accent-rgb), 0.02);padding:20px 24px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;border-bottom:2px solid var(--border-light);text-align:left}
    .report-table td{padding:20px 24px;border-bottom:1px solid var(--border-light);font-size:15px;color:var(--fg);vertical-align:middle;word-wrap:break-word}
    .report-table tr:last-child td{border-bottom:none}
    .report-table tr:hover td{background:rgba(var(--accent-rgb), 0.01)}
    
    .report-pill-badge{padding:8px 14px;border-radius:12px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;gap:8px;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.05)}
    .report-pill-badge.borrowed{background:var(--gold-glow);color:var(--gold)}
    .report-pill-badge.late{background:var(--red-glow);color:var(--red)}
    .report-pill-badge.returned{background:var(--teal-glow);color:var(--teal)}
    
    .report-empty-state{padding:80px 40px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:16px}
    .report-empty-icon{width:80px;height:80px;background:var(--bg-soft);border-radius:30px;display:flex;align-items:center;justify-content:center;color:var(--dim)}
    .report-empty-text{font-size:16px;font-weight:600;color:var(--muted)}
    
    .report-print-head{display:none;padding:30px 0;border-bottom:3px double #333;margin-bottom:20px;text-align:center}
    .report-print-header-content{display:flex;align-items:center;justify-content:center;gap:20px}
    .report-print-logo{width:60px;height:60px;border-radius:12px;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800}
    .report-print-info h1{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#000;margin:0}
    .report-print-info p{font-size:12px;color:#666;margin:4px 0 0 0}

    @media print {
        body{background:#fff!important;color:#000!important}
        .topbar,.sidebar,.report-actions,.report-filter-card,.report-tabs{display:none!important}
        .main-content{padding:0!important;margin:0!important}
        .report-page{gap:20px}
        .report-print-head{display:block}
        .report-section-card{border:1px solid #eee;box-shadow:none;break-inside:avoid}
        .report-table th{background:#f9f9f9!important;color:#333!important;border-bottom:1px solid #000!important}
        .report-stat-card, .report-usage-widget{border:1px solid #eee;box-shadow:none}
        .report-pill-badge{border:1px solid #eee;background:transparent!important}
    }
</style>

<div class="report-page">
    <div class="report-print-head">
        <div class="report-print-header-content">
            <div class="report-print-logo">{{ substr($appName, 0, 1) }}</div>
            <div class="report-print-info">
                <h1>{{ $appName }}</h1>
                <p>Laporan Operasional Perpustakaan</p>
                <p class="text-[10px]">{{ $reportMeta['range_label'] }}</p>
            </div>
        </div>
    </div>

    <div class="report-toolbar">
        <div>
            <h1 class="report-title">Laporan</h1>
        </div>
        <div class="report-actions">
            <button type="button" class="btn-report-action" onclick="window.print()">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
            </button>
            <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn-report-action">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Unduh Excel
            </a>
            <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn-report-action">
                <i data-lucide="file-text" class="w-4 h-4"></i> Unduh PDF
            </a>
        </div>
    </div>

    <section class="report-tabs">
        <a href="{{ route('admin.reports.index', ['period' => 'monthly', 'type' => 'all']) }}" class="report-tab {{ request('type') !== 'loans' && request('period') === 'monthly' ? 'active' : '' }}">Laporan Bulanan</a>
        <a href="{{ route('admin.reports.index', ['period' => 'yearly', 'type' => 'all']) }}" class="report-tab {{ request('type') !== 'loans' && request('period') === 'yearly' ? 'active' : '' }}">Laporan Tahunan</a>
        <a href="{{ route('admin.reports.index', ['period' => request('period', 'monthly'), 'type' => 'loans']) }}" class="report-tab {{ request('type') === 'loans' ? 'active' : '' }}">Statistik Penggunaan</a>
    </section>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filter-card" data-async="true" data-refresh-targets=".report-page">
        <div class="report-filter-grid">
            <div class="flex flex-col gap-2">
                <label class="report-micro">Jenis Laporan</label>
                <select name="type" class="form-select px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
                    <option value="all" @selected($filters['type'] === 'all')>Semua laporan</option>
                    <option value="books" @selected($filters['type'] === 'books')>Data buku</option>
                    <option value="loans" @selected($filters['type'] === 'loans')>Data peminjaman</option>
                    <option value="returns" @selected($filters['type'] === 'returns')>Data pengembalian</option>
                </select>
            </div>
            <div class="flex flex-col gap-2">
                <label class="report-micro">Periode</label>
                <select name="period" id="reportPeriod" class="form-select px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
                    <option value="daily" @selected($filters['period'] === 'daily')>Harian</option>
                    <option value="weekly" @selected($filters['period'] === 'weekly')>Mingguan</option>
                    <option value="monthly" @selected($filters['period'] === 'monthly')>Bulanan</option>
                    <option value="yearly" @selected($filters['period'] === 'yearly')>Tahunan</option>
                    <option value="custom" @selected($filters['period'] === 'custom')>Custom</option>
                </select>
            </div>
            <div class="js-custom-date flex flex-col gap-2">
                <label class="report-micro">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-input px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
            </div>
            <div class="js-custom-date flex flex-col gap-2">
                <label class="report-micro">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-input px-4 py-3 text-sm rounded-xl" onchange="this.form.requestSubmit()">
            </div>
            <button type="submit" class="btn-apply">Tampilkan</button>
        </div>
    </form>

    <section class="report-stats-container">
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box books"><i data-lucide="book"></i></div>
                <div class="report-stat-footer"><i data-lucide="calendar"></i> {{ $reportMeta['range_label'] }}</div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value">{{ number_format($reportStats['books']) }}</div>
                <div class="report-stat-label">Total Data Buku</div>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box loans"><i data-lucide="book-up-2"></i></div>
                <div class="report-stat-footer"><i data-lucide="info"></i> {{ $reportStats['active_loans'] }} masih aktif</div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value">{{ number_format($reportStats['loans']) }}</div>
                <div class="report-stat-label">Total Peminjaman</div>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box returns"><i data-lucide="book-down"></i></div>
                <div class="report-stat-footer"><i data-lucide="alert-circle"></i> {{ $reportStats['returned_late'] }} terlambat</div>
            </div>
            <div class="report-stat-content">
                <div class="report-stat-value">{{ number_format($reportStats['returns']) }}</div>
                <div class="report-stat-label">Total Pengembalian</div>
            </div>
        </div>
    </section>

    <section class="report-usage-row">
        <div class="report-usage-widget">
            <div class="report-usage-tag">Peminjam Aktif</div>
            <div class="report-usage-number">{{ number_format($usageStats['unique_borrowers']) }}</div>
            <div class="report-usage-desc">Anggota unik</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Buku Dipakai</div>
            <div class="report-usage-number">{{ number_format($usageStats['books_in_circulation']) }}</div>
            <div class="report-usage-desc">Sedang beredar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Selesai</div>
            <div class="report-usage-number">{{ number_format($usageStats['completed_returns']) }}</div>
            <div class="report-usage-desc">Pengembalian</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Terpopuler</div>
            <div class="report-usage-number" style="font-size: 16px; line-height: 1.4">{{ $usageStats['top_book'] }}</div>
            <div class="report-usage-desc">Judul buku</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag">Favorit</div>
            <div class="report-usage-number" style="font-size: 16px; line-height: 1.4">{{ $usageStats['top_category'] }}</div>
            <div class="report-usage-desc">Kategori</div>
        </div>
    </section>

    @if (in_array($filters['type'], ['all', 'books'], true))
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <h2 class="report-section-title-text">Data Buku</h2>
                    <p class="report-section-subtitle-text">Daftar koleksi buku yang terdaftar di perpustakaan pada periode laporan ini.</p>
                </div>
                <div class="report-section-count">{{ $bookReport->count() }} Judul</div>
            </div>
            @if ($bookReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Informasi Buku</th>
                                <th>Kategori</th>
                                <th>ISBN</th>
                                <th>Stok (Tersedia/Total)</th>
                                <th>Populer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookReport as $book)
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-900">{{ $book->title }}</span>
                                            <span class="text-xs text-slate2-500 mt-0.5">{{ $book->author }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="px-3 py-1 rounded-lg bg-slate2-50 text-slate2-700 text-xs font-bold border border-slate2-100">
                                            {{ $book->category?->name ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="font-mono text-xs text-slate2-600">{{ $book->isbn ?: '-' }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black {{ $book->stock_available > 0 ? 'text-teal-600' : 'text-red-600' }}">{{ $book->stock_available }}</span>
                                            <span class="text-slate2-300">/</span>
                                            <span class="text-sm text-slate2-500">{{ $book->stock_total }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-gold-600">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                            <span class="font-bold">{{ $book->loans_count }}x</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="book-x" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data buku pada periode ini.</p>
                </div>
            @endif
        </section>
    @endif

    @if (in_array($filters['type'], ['all', 'loans'], true))
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <h2 class="report-section-title-text">Data Peminjaman</h2>
                    <p class="report-section-subtitle-text">Riwayat transaksi peminjaman buku yang dilakukan oleh anggota perpustakaan.</p>
                </div>
                <div class="report-section-count">{{ $loanReport->count() }} Transaksi</div>
            </div>
            @if ($loanReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku & Peminjam</th>
                                <th>Waktu Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Petugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loanReport as $loan)
                                <tr>
                                    <td>
                                        <div class="flex flex-col gap-2">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate2-900 line-clamp-1">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</span>
                                                <span class="text-xs text-slate2-500">{{ $loan->book?->author ?? '-' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 pt-2 border-t border-dashed border-slate2-100">
                                                <div class="w-6 h-6 rounded-full bg-accent-glow text-accent flex items-center justify-center text-[10px] font-black">
                                                    {{ strtoupper(substr($loan->member?->name ?? 'P', 0, 1)) }}
                                                </div>
                                                <span class="text-xs font-semibold text-slate2-700">{{ $loan->member?->name ?? 'Peminjam' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-800 text-sm">{{ optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}</span>
                                            <span class="text-[10px] text-slate2-400 uppercase tracking-wider font-bold">Tanggal Pinjam</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-red-600 text-sm">{{ optional($loan->due_at)->translatedFormat('d M Y') ?? '-' }}</span>
                                            <span class="text-[10px] text-slate2-400 uppercase tracking-wider font-bold">Harus Kembali</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-xs font-medium text-slate2-600 bg-slate2-50 px-2 py-1 rounded border border-slate2-100">
                                            {{ $loan->processor?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="report-pill-badge {{ $loan->status }}">
                                            <i data-lucide="{{ $loan->status === 'returned' ? 'check-circle' : ($loan->status === 'late' ? 'alert-triangle' : 'clock') }}" class="w-3.5 h-3.5"></i>
                                            {{ $loan->status === 'late' ? 'Terlambat' : ($loan->status === 'returned' ? 'Selesai' : 'Dipinjam') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="file-warning" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data peminjaman pada periode ini.</p>
                </div>
            @endif
        </section>
    @endif

    @if (in_array($filters['type'], ['all', 'returns'], true))
        <section class="report-section-card">
            <div class="report-section-header">
                <div class="report-section-info">
                    <h2 class="report-section-title-text">Data Pengembalian</h2>
                    <p class="report-section-subtitle-text">Daftar buku yang telah dikembalikan oleh anggota ke perpustakaan.</p>
                </div>
                <div class="report-section-count">{{ $returnReport->count() }} Pengembalian</div>
            </div>
            @if ($returnReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku & Peminjam</th>
                                <th>Waktu Kembali</th>
                                <th>Petugas</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returnReport as $loan)
                                <tr>
                                    <td>
                                        <div class="flex flex-col gap-2">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate2-900 line-clamp-1">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</span>
                                                <span class="text-xs text-slate2-500">{{ $loan->book?->author ?? '-' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 pt-2 border-t border-dashed border-slate2-100">
                                                <span class="text-xs font-semibold text-slate2-700">{{ $loan->member?->name ?? 'Peminjam' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2 text-teal-600">
                                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                            <span class="font-black text-sm">{{ optional($loan->returned_at)->translatedFormat('d M Y') ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-xs font-medium text-slate2-600 bg-slate2-50 px-2 py-1 rounded border border-slate2-100">
                                            {{ $loan->processor?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($loan->status === 'late')
                                            <span class="text-xs text-red-500 font-bold italic">Dikembalikan Terlambat</span>
                                        @else
                                            <span class="text-xs text-slate2-400 italic">Tepat Waktu</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty-state">
                    <div class="report-empty-icon"><i data-lucide="clipboard-x" class="w-10 h-10"></i></div>
                    <p class="report-empty-text">Tidak ada data pengembalian pada periode ini.</p>
                </div>
            @endif
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const periodSelect = document.getElementById('reportPeriod');
        const customDateFields = document.querySelectorAll('.js-custom-date');

        function toggleCustomDates() {
            const isCustom = periodSelect.value === 'custom';
            customDateFields.forEach(field => {
                field.style.display = isCustom ? 'flex' : 'none';
            });
        }

        if (periodSelect) {
            periodSelect.addEventListener('change', toggleCustomDates);
            toggleCustomDates();
        }

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
