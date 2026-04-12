@extends('layouts.admin')

@section('content')
@php($title = 'Laporan')
@php($eyebrow = 'Superadmin dan Petugas')
@php($appName = \App\Models\Setting::valueOr('app_name', 'LibraVault'))
@php($appLogo = \App\Models\Setting::valueOr('app_logo'))
@php($appColor = \App\Models\Setting::valueOr('app_color', '#FAFAFA'))

<style>
    .report-page{display:flex;flex-direction:column;gap:24px}
    .report-toolbar{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .report-title{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .report-subtitle{font-size:14px;color:var(--muted);margin-top:6px;line-height:1.6}
    .report-actions{display:flex;gap:10px;flex-wrap:wrap}
    .btn-report-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border-radius:12px;background:#fff;border:1px solid var(--border-light);color:var(--fg);font-size:13px;font-weight:700;cursor:pointer;transition:.25s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm);text-decoration:none}
    .btn-report-action:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-1px);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-report-action:active{transform:translateY(0);box-shadow:0 4px 10px var(--accent-glow)}
    .report-filter-card,.report-section,.report-print-head{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-sm)}
    .report-filter-card{padding:22px}
    .report-filter-grid{display:grid;grid-template-columns:1.1fr .9fr .9fr .9fr auto;gap:12px;align-items:end}
    .report-micro{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}
    .report-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .report-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow-sm)}
    .report-stat-value{font-size:28px;font-weight:700;color:var(--fg);line-height:1}
    .report-stat-label{font-size:13px;color:var(--muted);margin-top:8px}
    .report-stat-sub{font-size:12px;color:var(--dim);margin-top:4px}
    .report-usage-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}
    .report-usage-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow-sm)}
    .report-usage-value{font-size:24px;font-weight:700;color:var(--fg);line-height:1.2}
    .report-usage-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}
    .report-shortcuts{display:flex;gap:10px;flex-wrap:wrap}
    .report-section{overflow:hidden}
    .report-section-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap}
    .report-section-title{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--fg)}
    .report-section-sub{font-size:13px;color:var(--muted);margin-top:4px}
    .report-badge{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:999px;background:var(--accent);color:#fff;font-size:11px;font-weight:700}
    .report-table-wrap{overflow-x:auto}
    .report-table{width:100%;border-collapse:collapse}
    .report-table th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--dim);border-bottom:1px solid var(--border)}
    .report-table td{padding:15px 16px;border-bottom:1px solid var(--border);font-size:14px;color:var(--fg);vertical-align:top}
    .report-table tr:last-child td{border-bottom:none}
    .report-empty{padding:42px 20px;text-align:center;color:var(--muted)}
    .report-book{display:flex;align-items:center;gap:12px;min-width:250px}
    .report-book-cover{width:44px;height:58px;border-radius:12px;overflow:hidden;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0}
    .report-book-title{font-size:14px;font-weight:700;color:var(--fg)}
    .report-book-sub{font-size:12px;color:var(--muted);margin-top:3px}
    .report-person{min-width:190px}
    .report-pill{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
    .report-pill.borrowed{background:var(--gold-light);color:var(--gold)}
    .report-pill.late{background:var(--red-light);color:var(--red)}
    .report-pill.returned{background:var(--teal-light);color:var(--teal)}
    .report-print-head{display:none;padding:30px 0;border-bottom:3px double #333;margin-bottom:20px;text-align:center}
    .report-print-header-content{display:flex;align-items:center;justify-content:center;gap:20px}
    .report-print-logo{width:80px;height:80px;object-fit:contain}
    .report-print-info{text-align:left}
    .report-print-title{font-size:24px;font-weight:900;text-transform:uppercase;margin:0;color:#000}
    .report-print-sub{font-size:14px;color:#333;margin-top:4px;font-weight:500}
    
    @media (max-width:1180px){.report-filter-grid{grid-template-columns:1fr 1fr}.report-stats,.report-usage-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:760px){.report-filter-grid,.report-stats,.report-usage-grid{grid-template-columns:1fr}.report-title{font-size:26px}}
    @media print{
        body{background:#fff;color:#000}
        .topbar,.sidebar,.side-mask,.report-filter-card,.report-actions,.report-shortcuts,.member-badge,.alert-box,.report-book-cover,.report-badge,.report-stats,.report-usage-grid{display:none!important}
        .main-area{margin:0!important;padding:0!important}
        .page-wrap{padding:0!important}
        .report-print-head{display:block}
        .report-section,.report-stat,.report-usage-card{box-shadow:none;border:none;padding:0;margin-bottom:30px}
        .report-section-head{padding:10px 0;border-bottom:2px solid #000;margin-bottom:15px}
        .report-section-title{font-size:18px}
        .report-table{width:100%;border:1px solid #000}
        .report-table th,.report-table td{border:1px solid #000;font-size:12px;padding:8px;color:#000}
        .report-table th{background:#eee!important;text-transform:uppercase}
        .report-book{display:block;min-width:auto}
        .report-book-title{font-size:12px}
        .report-book-sub{font-size:10px}
        .report-pill{padding:0;font-weight:normal;background:none!important;color:#000!important}
        .report-stats,.report-usage-grid{display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;margin-bottom:20px}
        .report-stat,.report-usage-card{border:1px solid #000;padding:10px;text-align:center}
        .report-stat-value,.report-usage-value{font-size:18px}
        .report-stat-label,.report-usage-label{font-size:10px}
    }
</style>

<div class="report-page">
    <div class="report-print-head">
        <div class="report-print-header-content">
            @if($appLogo)
                <img src="{{ asset($appLogo) }}" class="report-print-logo" alt="Logo">
            @endif
            <div class="report-print-info">
                <h1 class="report-print-title">{{ $appName }}</h1>
                <div class="report-print-sub">Laporan Perpustakaan | {{ $reportMeta['title'] }}</div>
                <div class="report-print-sub">Periode: {{ $reportMeta['range_label'] }} | Dicetak: {{ $reportMeta['printed_at'] }}</div>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Total Buku</th>
                        <th>Total Pinjam</th>
                        <th>Total Kembali</th>
                        <th>Terlambat</th>
                        <th>Peminjam Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $reportStats['books'] }}</td>
                        <td>{{ $reportStats['loans'] }}</td>
                        <td>{{ $reportStats['returns'] }}</td>
                        <td>{{ $reportStats['returned_late'] }}</td>
                        <td>{{ $usageStats['unique_borrowers'] }}</td>
                    </tr>
                </tbody>
            </table>
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

    <section class="report-shortcuts">
        <a href="{{ route('admin.reports.index', ['period' => 'monthly', 'type' => 'all']) }}" class="btn-report-action">Laporan Bulanan</a>
        <a href="{{ route('admin.reports.index', ['period' => 'yearly', 'type' => 'all']) }}" class="btn-report-action">Laporan Tahunan</a>
        <a href="{{ route('admin.reports.index', ['period' => request('period', 'monthly'), 'type' => 'loans']) }}" class="btn-report-action">Statistik Penggunaan</a>
    </section>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filter-card" data-async="true" data-refresh-targets=".report-page">
        <div class="report-filter-grid">
            <div>
                <div class="report-micro">Jenis Laporan</div>
                <select name="type" class="form-select px-3 py-3 text-sm" onchange="this.form.requestSubmit()">
                    <option value="all" @selected($filters['type'] === 'all')>Semua laporan</option>
                    <option value="books" @selected($filters['type'] === 'books')>Data buku</option>
                    <option value="loans" @selected($filters['type'] === 'loans')>Data peminjaman</option>
                    <option value="returns" @selected($filters['type'] === 'returns')>Data pengembalian</option>
                </select>
            </div>
            <div>
                <div class="report-micro">Periode</div>
                <select name="period" id="reportPeriod" class="form-select px-3 py-3 text-sm" onchange="this.form.requestSubmit()">
                    <option value="daily" @selected($filters['period'] === 'daily')>Harian</option>
                    <option value="weekly" @selected($filters['period'] === 'weekly')>Mingguan</option>
                    <option value="monthly" @selected($filters['period'] === 'monthly')>Bulanan</option>
                    <option value="yearly" @selected($filters['period'] === 'yearly')>Tahunan</option>
                    <option value="custom" @selected($filters['period'] === 'custom')>Custom</option>
                </select>
            </div>
            <div class="js-custom-date">
                <div class="report-micro">Dari Tanggal</div>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-input px-3 py-3 text-sm" onchange="this.form.requestSubmit()">
            </div>
            <div class="js-custom-date">
                <div class="report-micro">Sampai Tanggal</div>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-input px-3 py-3 text-sm" onchange="this.form.requestSubmit()">
            </div>
            <div>
                <button type="submit" class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Terapkan</button>
            </div>
        </div>
    </form>

    <section class="report-stats">
        <div class="report-stat">
            <div class="report-stat-value">{{ $reportStats['books'] }}</div>
            <div class="report-stat-label">Data Buku</div>
            <div class="report-stat-sub">{{ $reportMeta['range_label'] }}</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-value">{{ $reportStats['loans'] }}</div>
            <div class="report-stat-label">Data Peminjaman</div>
            <div class="report-stat-sub">{{ $reportStats['active_loans'] }} transaksi masih aktif</div>
        </div>
        <div class="report-stat">
            <div class="report-stat-value">{{ $reportStats['returns'] }}</div>
            <div class="report-stat-label">Data Pengembalian</div>
            <div class="report-stat-sub">{{ $reportStats['returned_late'] }} kembali terlambat</div>
        </div>
    </section>

    <section class="report-usage-grid">
        <div class="report-usage-card">
            <div class="report-usage-label">Peminjam Aktif</div>
            <div class="report-usage-value">{{ number_format($usageStats['unique_borrowers']) }}</div>
        </div>
        <div class="report-usage-card">
            <div class="report-usage-label">Buku Dipakai</div>
            <div class="report-usage-value">{{ number_format($usageStats['books_in_circulation']) }}</div>
        </div>
        <div class="report-usage-card">
            <div class="report-usage-label">Pengembalian Selesai</div>
            <div class="report-usage-value">{{ number_format($usageStats['completed_returns']) }}</div>
        </div>
        <div class="report-usage-card">
            <div class="report-usage-label">Buku Terpopuler</div>
            <div class="report-usage-value">{{ $usageStats['top_book'] }}</div>
        </div>
        <div class="report-usage-card">
            <div class="report-usage-label">Kategori Terbanyak Dipinjam</div>
            <div class="report-usage-value">{{ $usageStats['top_category'] }}</div>
        </div>
    </section>

    @if (in_array($filters['type'], ['all', 'books'], true))
        <section class="report-section">
            <div class="report-section-head">
                <div>
                    <div class="report-section-title">Data Buku</div>
                    <div class="report-section-sub">{{ $reportMeta['title'] }} untuk koleksi buku perpustakaan.</div>
                </div>
                <div class="report-badge">{{ $bookReport->count() }} buku</div>
            </div>
            @if ($bookReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Kategori</th>
                                <th>ISBN</th>
                                <th>Stok</th>
                                <th>Total Dipinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookReport as $book)
                                <tr>
                                    <td>
                                        <div class="report-book">
                                            <div>
                                                <div class="report-book-title">{{ $book->title }}</div>
                                                <div class="report-book-sub">{{ $book->author }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $book->category?->name ?? '-' }}</td>
                                    <td>{{ $book->isbn ?: '-' }}</td>
                                    <td>{{ $book->stock_available }}/{{ $book->stock_total }}</td>
                                    <td>{{ $book->loans_count }}x</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty">Tidak ada data buku pada periode ini.</div>
            @endif
        </section>
    @endif

    @if (in_array($filters['type'], ['all', 'loans'], true))
        <section class="report-section">
            <div class="report-section-head">
                <div>
                    <div class="report-section-title">Data Peminjaman</div>
                    <div class="report-section-sub">Transaksi yang tanggal pinjamnya masuk dalam periode laporan.</div>
                </div>
                <div class="report-badge">{{ $loanReport->count() }} transaksi</div>
            </div>
            @if ($loanReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Petugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loanReport as $loan)
                                <tr>
                                    <td>
                                        <div class="report-book">
                                            <div>
                                                <div class="report-book-title">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</div>
                                                <div class="report-book-sub">{{ $loan->book?->author ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="report-person">
                                        <div class="report-book-title">{{ $loan->member?->name ?? 'Peminjam tidak ditemukan' }}</div>
                                        <div class="report-book-sub">{{ $loan->member?->username ?? '-' }}{{ $loan->member?->academicLabel() ? ' | '.$loan->member->academicLabel() : '' }}</div>
                                    </td>
                                    <td>{{ optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>{{ optional($loan->due_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>{{ $loan->processor?->name ?? '-' }}</td>
                                    <td>
                                        <span class="report-pill {{ $loan->status }}">
                                            {{ $loan->status === 'late' ? 'Terlambat' : ($loan->status === 'returned' ? 'Dikembalikan' : 'Dipinjam') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty">Tidak ada data peminjaman pada periode ini.</div>
            @endif
        </section>
    @endif

    @if (in_array($filters['type'], ['all', 'returns'], true))
        <section class="report-section">
            <div class="report-section-head">
                <div>
                    <div class="report-section-title">Data Pengembalian</div>
                    <div class="report-section-sub">Transaksi yang sudah dikembalikan dalam periode laporan.</div>
                </div>
                <div class="report-badge">{{ $returnReport->count() }} pengembalian</div>
            </div>
            @if ($returnReport->count())
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Peminjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returnReport as $loan)
                                <tr>
                                    <td>
                                        <div class="report-book">
                                            <div>
                                                <div class="report-book-title">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</div>
                                                <div class="report-book-sub">{{ $loan->book?->author ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="report-person">
                                        <div class="report-book-title">{{ $loan->member?->name ?? 'Peminjam tidak ditemukan' }}</div>
                                        <div class="report-book-sub">{{ $loan->member?->username ?? '-' }}</div>
                                    </td>
                                    <td>{{ optional($loan->returned_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>{{ $loan->processor?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty">Tidak ada data pengembalian pada periode ini.</div>
            @endif
        </section>
    @endif
</div>

<script>
    const reportPeriod = document.getElementById('reportPeriod');
    const customDateFields = document.querySelectorAll('.js-custom-date');

    function syncCustomDateFields() {
        const isCustom = reportPeriod.value === 'custom';

        customDateFields.forEach(function (field) {
            field.style.display = isCustom ? 'block' : 'none';
        });
    }

    reportPeriod.addEventListener('change', syncCustomDateFields);
    syncCustomDateFields();
</script>
@endsection
