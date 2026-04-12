@extends('layouts.admin')

@section('content')
@php($title = 'Riwayat Peminjaman')
@php($eyebrow = 'Akun Peminjam')

<style>
    .history-shell{display:flex;flex-direction:column;gap:24px}
    .history-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .history-stat{background:#fff;border:1px solid var(--border);border-radius:18px;padding:18px 20px;box-shadow:var(--shadow-sm)}
    .history-stat-value{font-size:28px;font-weight:700;color:var(--fg)}
    .history-stat-label{font-size:13px;color:var(--muted);margin-top:4px}
    .history-stat-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:700;margin-top:10px}
    .history-stat-status.active{background:var(--teal-light);color:var(--teal)}
    .history-stat-status.sanctioned{background:var(--red-light);color:var(--red)}
    .history-alert{padding:16px 18px;border-radius:18px;border:1px solid rgba(196,69,54,.18);background:rgba(253,240,238,.9);color:#9f2d20}
    .history-list{background:#fff;border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-sm);overflow:hidden}
    .history-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:20px 22px;border-bottom:1px solid var(--border)}
    .history-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--fg)}
    .history-sub{font-size:13px;color:var(--muted);line-height:1.7;margin-top:6px}
    .history-grid{display:grid;gap:16px;padding:20px 22px}
    .history-card{border:1px solid var(--border);border-radius:18px;background:#fff;padding:18px}
    .history-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}
    .history-book{font-size:18px;font-weight:700;color:var(--fg)}
    .history-author{font-size:12px;color:var(--muted);margin-top:4px}
    .history-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:700}
    .history-status.requested{background:rgba(212,160,58,.12);color:#a96808}
    .history-status.borrowed{background:rgba(45,134,89,.1);color:#2d8659}
    .history-status.late{background:rgba(196,69,54,.1);color:#c44536}
    .history-status.returned{background:rgba(15,76,92,.08);color:#0f4c5c}
    .history-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:16px}
    .history-meta-box{padding:12px;border-radius:14px;background:#f8f6f1;border:1px solid #eee3d8}
    .history-meta-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7b6d60}
    .history-meta-value{font-size:13px;color:#1a2e35;margin-top:6px;line-height:1.5}
    .history-note{margin-top:14px;font-size:12px;color:var(--muted);line-height:1.6}
    .history-empty{padding:38px 20px;text-align:center;color:var(--muted)}
    @media (max-width:1024px){.history-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:768px){.history-stats,.history-meta{grid-template-columns:1fr}.history-head,.history-top{flex-direction:column}.history-grid{padding:16px}}
</style>

<div class="history-shell">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Riwayat Peminjaman</h1>
            <p class="member-subtitle">Cek buku yang sedang dipinjam, riwayat transaksi, dan status akun Anda.</p>
        </div>
    </div>

    <section class="history-stats">
        <article class="history-stat">
            <div class="history-stat-value">{{ $borrowerHistoryStats['active_loans'] }}</div>
            <div class="history-stat-label">Buku sedang dipinjam</div>
        </article>
        <article class="history-stat">
            <div class="history-stat-value">{{ $borrowerHistoryStats['requested'] }}</div>
            <div class="history-stat-label">Pengajuan menunggu</div>
        </article>
        <article class="history-stat">
            <div class="history-stat-value">{{ $borrowerHistoryStats['returned'] }}</div>
            <div class="history-stat-label">Riwayat selesai</div>
        </article>
        <article class="history-stat">
            <div class="history-stat-value">{{ auth()->user()?->name }}</div>
            <div class="history-stat-label">Status akun peminjam</div>
            <div class="history-stat-status {{ $borrowerActiveSanction ? 'sanctioned' : 'active' }}">{{ $borrowerHistoryStats['account_status'] }}</div>
        </article>
    </section>

    @if ($borrowerActiveSanction)
        <div class="history-alert">
            Akun Anda sedang kena sanksi.
            @if ($borrowerActiveSanction->ends_at)
                Masa sanksi sampai {{ $borrowerActiveSanction->ends_at->translatedFormat('d M Y') }}.
            @endif
            Selama sanksi aktif, Anda belum bisa meminjam buku lagi.
        </div>
    @endif

    <section class="history-list">
        <div class="history-head">
            <div>
                <div class="history-title">Daftar Riwayat</div>
                <div class="history-sub">Semua pengajuan, buku yang sedang dipinjam, keterlambatan, dan riwayat selesai tampil di halaman ini.</div>
            </div>
        </div>

        @if ($borrowerLoans->count())
            <div class="history-grid">
                @foreach ($borrowerLoans as $loan)
                    <article class="history-card">
                        <div class="history-top">
                            <div>
                                <div class="history-book">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</div>
                                <div class="history-author">{{ $loan->book?->author ?? 'Penulis tidak tersedia' }}</div>
                            </div>
                            <span class="history-status {{ $loan->status }}">
                                {{
                                    match ($loan->status) {
                                        'requested' => 'Menunggu',
                                        'borrowed' => 'Dipinjam',
                                        'late' => 'Terlambat',
                                        'returned' => 'Dikembalikan',
                                        default => ucfirst($loan->status),
                                    }
                                }}
                            </span>
                        </div>

                        <div class="history-meta">
                            <div class="history-meta-box">
                                <div class="history-meta-label">Tanggal Pinjam</div>
                                <div class="history-meta-value">{{ optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}</div>
                            </div>
                            <div class="history-meta-box">
                                <div class="history-meta-label">Batas Pengembalian</div>
                                <div class="history-meta-value">{{ optional($loan->due_at)->translatedFormat('d M Y') ?? '-' }}</div>
                            </div>
                            <div class="history-meta-box">
                                <div class="history-meta-label">Tanggal Kembali</div>
                                <div class="history-meta-value">{{ optional($loan->returned_at)->translatedFormat('d M Y') ?? 'Belum dikembalikan' }}</div>
                            </div>
                            <div class="history-meta-box">
                                <div class="history-meta-label">Diproses Oleh</div>
                                <div class="history-meta-value">{{ $loan->processor?->name ?? ($loan->status === 'requested' ? 'Menunggu petugas' : '-') }}</div>
                            </div>
                        </div>

                        <div class="history-note">
                            {{ $loan->notes ?: 'Tidak ada catatan tambahan.' }}
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="padding:0 22px 20px;">{{ $borrowerLoans->links() }}</div>
        @else
            <div class="history-empty">Belum ada riwayat peminjaman di akun ini.</div>
        @endif
    </section>
</div>
@endsection
