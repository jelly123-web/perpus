@extends('layouts.admin')

@section('content')
@php($title = 'Peminjaman Buku')
@php($eyebrow = 'Petugas Perpustakaan')

<style>
    .loan-shell{display:grid;grid-template-columns:minmax(350px,450px) minmax(0,1fr);gap:32px;width:100%}
    .loan-stack{display:flex;flex-direction:column;gap:32px}
    .loan-card{background:#fff;border:1px solid var(--border-light);border-radius:32px;box-shadow:var(--shadow-sm);overflow:hidden;transition:.3s ease}
    .loan-card:hover{box-shadow:var(--shadow-md)}
    .loan-card-header{padding:32px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;gap:20px;background:linear-gradient(to right, var(--bg-soft), transparent)}
    .loan-card-title{font-family:'Playfair Display',serif;font-size:28px;font-weight:800;color:var(--fg);margin:0}
    .loan-card-subtitle{font-size:15px;color:var(--muted);margin-top:6px;line-height:1.5}
    .loan-card-body{padding:32px}
    
    .loan-form-grid{display:grid;gap:24px}
    .loan-field{display:flex;flex-direction:column;gap:10px}
    .loan-label{font-size:13px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}
    .loan-input-group{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    
    .loan-item-card{padding:24px;border-radius:24px;background:var(--bg-soft);border:1px solid var(--border-light);display:flex;flex-direction:column;gap:16px;transition:.2s ease}
    .loan-item-card:hover{background:#fff;transform:translateY(-2px);box-shadow:var(--shadow-sm)}
    .loan-item-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .loan-item-name{font-size:16px;font-weight:800;color:var(--fg)}
    .loan-item-info{font-size:14px;color:var(--muted);line-height:1.6}
    .loan-item-badge{padding:6px 14px;border-radius:10px;font-size:12px;font-weight:800;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.05)}
    .loan-item-badge.pending{background:var(--gold-glow);color:var(--gold)}
    .loan-item-badge.active{background:var(--red-glow);color:var(--red)}
    .loan-item-badge.done{background:var(--teal-glow);color:var(--teal)}
    
    .loan-table-wrap{width:100%;overflow-x:auto}
    .loan-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .loan-table th{background:rgba(var(--accent-rgb), 0.02);padding:20px 24px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;border-bottom:2px solid var(--border-light);text-align:left}
    .loan-table td{padding:20px 24px;border-bottom:1px solid var(--border-light);font-size:15px;color:var(--fg);vertical-align:middle;word-wrap:break-word}
    .loan-table tr:hover td{background:rgba(var(--accent-rgb), 0.01)}
    
    .loan-book-box{display:flex;align-items:center;gap:16px}
    .loan-book-cover-mini{width:48px;height:64px;border-radius:12px;overflow:hidden;background:var(--accent-glow);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:18px;flex-shrink:0;box-shadow:var(--shadow-sm)}
    .loan-book-title-text{font-weight:800;color:var(--fg);font-size:15px;line-clamp:1;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden}
    .loan-book-author-text{font-size:13px;color:var(--muted);margin-top:2px}
    
    .loan-status-select{width:100%;padding:12px 16px;border-radius:14px;border:1px solid var(--border-light);background:#fff;font-size:14px;font-weight:700;color:var(--fg);cursor:pointer;transition:.2s ease;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center}
    .loan-status-select:hover{border-color:var(--accent);box-shadow:0 0 15px var(--accent-glow)}
    
    .btn-loan-submit{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 24px;border-radius:16px;background:#fff;border:1px solid var(--border-light);color:var(--fg);font-size:14px;font-weight:700;cursor:pointer;transition:.3s cubic-bezier(.4,0,.2,1);box-shadow:var(--shadow-sm);width:100%;height:52px}
    .btn-loan-submit:hover{background:var(--bg-soft);color:var(--accent);border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px var(--accent-glow)}
    .btn-loan-submit:active{transform:translateY(0);box-shadow:0 4px 10px var(--accent-glow)}

    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px}
    .report-usage-widget{background:#fff;border:1px solid var(--border-light);border-radius:24px;padding:24px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:12px;min-height:130px;transition:.2s ease}
    .report-usage-widget:hover{background:var(--bg-soft)}
    .report-usage-tag{font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.12em}
    .report-usage-number{font-size:24px;font-weight:800;color:var(--fg)}
    .report-usage-desc{font-size:14px;color:var(--dim);font-weight:500}

    @media (max-width:1100px){.loan-shell{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="member-toolbar" style="border-bottom: 1px solid var(--border-light); padding-bottom: 24px; margin-bottom: 32px;">
        <div>
            <h1 class="font-display member-title" style="font-size: 36px; font-weight: 800;">Peminjaman Buku</h1>
            <p class="member-subtitle" style="font-size: 16px; color: var(--muted); margin-top: 8px;">Input data peminjaman, catat nama peminjam, lalu tentukan tanggal pinjam dan tanggal kembali.</p>
        </div>
        <div class="btn-report-action" style="cursor: default; pointer-events: none;">
            <i data-lucide="book-up-2" class="w-4 h-4"></i> Akses petugas
        </div>
    </div>

    <section id="loanStatsWrap" class="report-usage-row" style="margin-bottom: 32px;">
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent);">Pengajuan Baru</div>
            <div class="report-usage-number">{{ $loanStats['requested'] }}</div>
            <div class="report-usage-desc">Menunggu proses</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--gold);">Total Pinjam</div>
            <div class="report-usage-number">{{ $loanStats['total'] }}</div>
            <div class="report-usage-desc">Semua transaksi</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--teal);">Sedang Dipinjam</div>
            <div class="report-usage-number">{{ $loanStats['borrowed'] }}</div>
            <div class="report-usage-desc">Buku di luar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent-dark);">Sudah Kembali</div>
            <div class="report-usage-number">{{ $loanStats['returned'] }}</div>
            <div class="report-usage-desc">Selesai pinjam</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--red);">Terlambat</div>
            <div class="report-usage-number">{{ $loanStats['late'] }}</div>
            <div class="report-usage-desc">Perlu tindakan</div>
        </div>
    </section>

    <div id="loanPageWrap" class="loan-shell">
        <div class="loan-stack">
            <!-- Section: Pengajuan -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Pengajuan</h2>
                        <p class="loan-card-subtitle">Pengajuan dari akun peminjam.</p>
                    </div>
                    <span class="loan-item-badge pending">{{ $requestedLoans->count() }} Menunggu</span>
                </div>
                <div class="loan-card-body">
                    <div class="flex flex-col gap-4">
                        @forelse ($requestedLoans as $requestedLoan)
                            <div class="loan-item-card">
                                <div class="loan-item-head">
                                    <div>
                                        <div class="loan-item-name">{{ $requestedLoan->member?->name ?? 'Peminjam' }}</div>
                                        <div class="loan-item-info mt-1">
                                            <strong>{{ $requestedLoan->book?->title ?? 'Buku' }}</strong>
                                        </div>
                                    </div>
                                    <span class="loan-item-badge pending">Sistem</span>
                                </div>
                                <div class="loan-item-info">
                                    Pinjam: {{ optional($requestedLoan->borrowed_at)->translatedFormat('d M Y') }}<br>
                                    Batas: {{ optional($requestedLoan->due_at)->translatedFormat('d M Y') }}
                                </div>
                                @if($requestedLoan->notes)
                                    <div class="p-3 rounded-xl bg-white border border-slate2-100 text-xs italic text-slate2-500">
                                        "{{ $requestedLoan->notes }}"
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="report-empty-state" style="padding: 40px 20px;">
                                <div class="report-empty-icon"><i data-lucide="inbox"></i></div>
                                <p class="report-empty-text">Belum ada pengajuan baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section: Input Peminjaman -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Input Pinjam</h2>
                        <p class="loan-card-subtitle">Catat transaksi peminjaman baru.</p>
                    </div>
                </div>
                <div class="loan-card-body">
                    <form method="POST" action="{{ route('admin.loans.store') }}" class="loan-form-grid" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        @csrf
                        <div class="loan-field">
                            <label class="loan-label">Buku</label>
                            <select name="book_id" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                <option value="">Pilih buku</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}">{{ $book->title }} (stok {{ $book->stock_available }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label">Peminjam</label>
                            <select name="member_id" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                <option value="">Pilih peminjam</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} {{ $member->academicLabel() ? ' | '.$member->academicLabel() : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="loan-input-group">
                            <div class="loan-field">
                                <label class="loan-label">Tgl Pinjam</label>
                                <input id="loanBorrowedAt" type="date" name="borrowed_at" class="form-input px-4 py-3 text-sm rounded-xl" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="loan-field">
                                <label class="loan-label">Tgl Kembali</label>
                                <input id="loanDueAt" type="date" name="due_at" class="form-input px-4 py-3 text-sm rounded-xl bg-slate2-50" value="{{ now()->addDay()->toDateString() }}" required readonly>
                            </div>
                        </div>
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Simpan Peminjaman
                        </button>
                    </form>
                </div>
            </div>

            <!-- Section: Pengembalian -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Pengembalian</h2>
                        <p class="loan-card-subtitle">Proses buku yang dikembalikan.</p>
                    </div>
                </div>
                <div class="loan-card-body">
                    <form method="POST" action="{{ route('admin.loans.return') }}" class="loan-form-grid" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        @csrf
                        <div class="loan-field">
                            <label class="loan-label">Transaksi Aktif</label>
                            <select name="loan_id" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                <option value="">Pilih transaksi</option>
                                @foreach ($activeLoans as $activeLoan)
                                    <option value="{{ $activeLoan->id }}">
                                        {{ $activeLoan->member?->name }} - {{ $activeLoan->book?->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label">Tgl Kembali</label>
                            <input type="date" name="returned_at" class="form-input px-4 py-3 text-sm rounded-xl" value="{{ now()->toDateString() }}" required>
                        </div>
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Simpan Pengembalian
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="loan-stack">
            <!-- Section: Daftar Transaksi -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Daftar Peminjaman Buku</h2>
                        <p class="loan-card-subtitle">Semua riwayat transaksi perpustakaan.</p>
                    </div>
                    <div class="btn-report-action" style="cursor: default; pointer-events: none;">
                        <i data-lucide="history" class="w-4 h-4"></i> {{ $loans->total() }} Transaksi
                    </div>
                </div>
                <div class="loan-table-wrap">
                    <table class="loan-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Buku</th>
                                <th style="width: 25%;">Peminjam</th>
                                <th style="width: 20%;">Waktu</th>
                                <th style="width: 20%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loans as $loan)
                                <tr>
                                    <td>
                                        <div class="loan-book-box">
                                            <div class="loan-book-cover-mini">
                                                {{ strtoupper(substr($loan->book?->title ?? 'B', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="loan-book-title-text">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</div>
                                                <div class="loan-book-author-text">{{ $loan->book?->author ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-900">{{ $loan->member?->name ?? 'Peminjam' }}</span>
                                            <span class="text-xs text-slate2-500 mt-0.5">{{ $loan->member?->academicLabel() ?? 'Anggota' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-800 text-sm">{{ optional($loan->borrowed_at)->translatedFormat('d M Y') }}</span>
                                            <span class="text-[10px] text-slate2-400 uppercase tracking-wider font-black mt-1">Hingga {{ optional($loan->returned_at ?? $loan->due_at)->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.loans.update', $loan) }}" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="loan-status-select" onchange="this.form.requestSubmit()">
                                                <option value="requested" @selected($loan->status === 'requested')>Menunggu</option>
                                                <option value="borrowed" @selected($loan->status === 'borrowed')>Dipinjam</option>
                                                <option value="late" @selected($loan->status === 'late')>Terlambat</option>
                                                <option value="returned" @selected($loan->status === 'returned')>Selesai</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($loans->hasPages())
                    <div class="p-6 border-t border-slate2-100">
                        {{ $loans->links() }}
                    </div>
                @endif
            </div>

            <!-- Section: Monitoring Sanksi -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Monitoring Sanksi</h2>
                        <p class="loan-card-subtitle">Status peminjam yang sedang dalam masa sanksi.</p>
                    </div>
                </div>
                <div class="loan-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($sanctionMonitoring as $monitoring)
                            <div class="loan-item-card">
                                <div class="loan-item-head">
                                    <div>
                                        <div class="loan-item-name">{{ $monitoring->member?->name ?? 'Peminjam' }}</div>
                                        <div class="loan-item-info mt-1 text-red-600 font-bold">
                                            {{ $monitoring->reason }}
                                        </div>
                                    </div>
                                    <span class="loan-item-badge {{ $monitoring->monitoring_state === 'active' ? 'active' : 'done' }}">
                                        {{ $monitoring->monitoring_state === 'active' ? 'Disanksi' : 'Selesai' }}
                                    </span>
                                </div>
                                <div class="loan-item-info">
                                    @if($monitoring->ends_at)
                                        Berakhir: {{ optional($monitoring->ends_at)->translatedFormat('d M Y') }}
                                    @else
                                        Durasi: {{ $monitoring->duration_days }} hari
                                    @endif
                                </div>
                                @if ($monitoring->monitoring_state !== 'completed')
                                    <form method="POST" action="{{ route('admin.loans.sanctions.update', $monitoring) }}" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button class="btn-report-action w-full mt-2" type="submit" style="font-size: 11px; height: 36px;">
                                            Tandai Aktif Kembali
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full report-empty-state">
                                <div class="report-empty-icon"><i data-lucide="shield-check"></i></div>
                                <p class="report-empty-text">Tidak ada sanksi aktif saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function syncLoanDueAt() {
        const loanBorrowedAt = document.getElementById('loanBorrowedAt');
        const loanDueAt = document.getElementById('loanDueAt');

        if (!loanBorrowedAt || !loanDueAt || !loanBorrowedAt.value) {
            return;
        }

        const borrowedDate = new Date(loanBorrowedAt.value + 'T00:00:00');
        borrowedDate.setDate(borrowedDate.getDate() + 1);
        loanDueAt.value = borrowedDate.toISOString().slice(0, 10);
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'loanBorrowedAt') {
            syncLoanDueAt();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        syncLoanDueAt();
    });

    document.addEventListener('async:refreshed', function (event) {
        if ((event.detail?.selectors || []).includes('#loanPageWrap')) {
            syncLoanDueAt();
        }
    });
</script>
@endsection
