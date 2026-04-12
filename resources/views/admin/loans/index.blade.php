@extends('layouts.admin')

@section('content')
@php($title = 'Peminjaman Buku')
@php($eyebrow = 'Petugas Perpustakaan')

<style>
    .loan-shell{display:grid;grid-template-columns:minmax(320px,430px) minmax(0,1fr);gap:20px}
    .loan-stack{display:flex;flex-direction:column;gap:20px}
    .loan-add,.loan-list{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-sm)}
    .loan-add{padding:24px}
    .loan-add-title{font-family:'Playfair Display',serif;font-size:34px;font-weight:700;letter-spacing:-.03em;color:var(--fg)}
    .loan-list{padding:20px}
    .loan-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .loan-list-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:var(--fg)}
    .loan-form-grid{display:grid;gap:14px}
    .loan-field{display:flex;flex-direction:column;gap:8px}
    .loan-label{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
    .loan-help{font-size:12px;color:var(--muted);line-height:1.6}
    .loan-return-preview{padding:14px 16px;border-radius:16px;background:var(--bg-soft);border:1px solid var(--border)}
    .loan-return-preview-title{font-size:12px;font-weight:700;color:var(--fg)}
    .loan-return-preview-sub{font-size:12px;color:var(--muted);margin-top:6px;line-height:1.6}
    .loan-sanction-list{display:flex;flex-direction:column;gap:12px;margin-top:16px}
    .loan-sanction-item{padding:14px 16px;border-radius:16px;background:#fff;border:1px solid var(--border)}
    .loan-sanction-type{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:var(--gold-light);color:var(--accent);font-size:11px;font-weight:700}
    .loan-sanction-reason{font-size:13px;color:var(--fg);margin-top:10px;line-height:1.6}
    .loan-sanction-meta{font-size:12px;color:var(--muted);margin-top:8px;line-height:1.6}
    .loan-monitoring-list{display:flex;flex-direction:column;gap:12px}
    .loan-monitoring-item{padding:14px 16px;border-radius:16px;background:#fff;border:1px solid var(--border)}
    .loan-monitoring-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .loan-monitoring-name{font-size:14px;font-weight:700;color:var(--fg)}
    .loan-monitoring-sub{font-size:12px;color:var(--muted);margin-top:4px;line-height:1.6}
    .loan-monitoring-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:700}
    .loan-monitoring-status.active{background:var(--red-light);color:var(--red)}
    .loan-monitoring-status.completed{background:var(--teal-light);color:var(--teal)}
    .loan-monitoring-status.expired{background:rgba(212,160,58,.12);color:var(--orange)}
    .loan-table-wrap{overflow-x:auto}
    .loan-table{width:100%;border-collapse:collapse}
    .loan-table th{padding:12px 14px;border-bottom:1px solid var(--border);text-align:left;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
    .loan-table td{padding:16px 14px;border-bottom:1px solid var(--border);font-size:14px;vertical-align:top}
    .loan-table tr:last-child td{border-bottom:none}
    .loan-book{display:flex;align-items:center;gap:12px;min-width:220px}
    .loan-cover{width:44px;height:58px;border-radius:12px;overflow:hidden;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0}
    .loan-book-title{font-size:14px;font-weight:700;color:var(--fg)}
    .loan-book-sub{font-size:12px;color:var(--muted);margin-top:3px}
    .loan-member{min-width:180px}
    .loan-member-name{font-size:14px;font-weight:700;color:var(--fg)}
    .loan-member-sub{font-size:12px;color:var(--muted);margin-top:3px}
    .loan-date{font-size:13px;color:var(--fg)}
    .loan-date-sub{display:block;font-size:12px;color:var(--muted);margin-top:4px}
    .loan-late{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:var(--red-light);color:var(--red);font-size:12px;font-weight:700}
    .loan-safe{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:var(--teal-light);color:var(--teal);font-size:12px;font-weight:700}
    .loan-status{width:100%;border:1px solid var(--border);border-radius:10px;background:#fff;padding:10px 12px;font-size:13px;color:var(--fg)}
    .loan-empty{border:1px dashed var(--border-light);border-radius:18px;padding:40px 20px;text-align:center;color:var(--muted);background:var(--bg-soft)}
    @media (max-width:1100px){.loan-shell{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Peminjaman Buku</h1>
            <p class="member-subtitle">Input data peminjaman, catat nama peminjam, lalu tentukan tanggal pinjam dan tanggal kembali.</p>
        </div>
        <div class="member-badge"><i data-lucide="book-up-2" class="w-3.5 h-3.5"></i> Akses petugas</div>
    </div>

    <section id="loanStatsWrap" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:rgba(212,160,58,.12);color:var(--orange);"><i data-lucide="inbox" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $loanStats['requested'] }}</div><div class="member-mini-label">Pengajuan Baru</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--accent);color:#fff;"><i data-lucide="files" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $loanStats['total'] }}</div><div class="member-mini-label">Total Peminjaman</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="book-up-2" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $loanStats['borrowed'] }}</div><div class="member-mini-label">Sedang Dipinjam</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="badge-check" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $loanStats['returned'] }}</div><div class="member-mini-label">Sudah Kembali</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--red-light);color:var(--red);"><i data-lucide="triangle-alert" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value">{{ $loanStats['late'] }}</div><div class="member-mini-label">Terlambat</div></div>
        </div>
    </section>

    <div id="loanPageWrap" class="loan-shell">
        <div class="loan-stack">
            <div class="loan-add">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="loan-add-title">Pengajuan dari Peminjam</div>
                        <div class="loan-help mt-2">Daftar ini berisi pengajuan pinjam yang masuk lewat akun peminjam dan menunggu diproses petugas.</div>
                    </div>
                    <div class="member-badge"><i data-lucide="inbox" class="w-3.5 h-3.5"></i> Menunggu</div>
                </div>

                <div class="loan-sanction-list mt-5">
                    @forelse ($requestedLoans as $requestedLoan)
                        <div class="loan-sanction-item">
                            <div class="loan-monitoring-head">
                                <div>
                                    <div class="loan-monitoring-name">{{ $requestedLoan->member?->name ?? 'Peminjam tidak ditemukan' }}</div>
                                    <div class="loan-monitoring-sub">
                                        {{ $requestedLoan->book?->title ?? 'Buku tidak ditemukan' }}
                                        | Pinjam {{ optional($requestedLoan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}
                                        | Batas kembali {{ optional($requestedLoan->due_at)->translatedFormat('d M Y') ?? '-' }}
                                        @if ($requestedLoan->member?->academicLabel())
                                            | {{ $requestedLoan->member->academicLabel() }}
                                        @endif
                                    </div>
                                </div>
                                <div class="loan-monitoring-status expired">Pengajuan sistem</div>
                            </div>
                            <div class="loan-sanction-reason">{{ $requestedLoan->notes ?? 'Tanpa catatan dari peminjam.' }}</div>
                        </div>
                    @empty
                        <div class="loan-return-preview">
                            <div class="loan-return-preview-title">Belum ada pengajuan baru</div>
                            <div class="loan-return-preview-sub">Kalau peminjam mengajukan pinjam lewat sistem, datanya akan tampil di sini.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="loan-add">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="loan-add-title">Input Peminjaman</div>
                        <div class="loan-help mt-2">Isi data transaksi peminjaman buku untuk anggota perpustakaan. Batas waktu pinjam otomatis 1 hari dari tanggal pinjam.</div>
                    </div>
                    <div class="member-badge"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Transaksi Baru</div>
                </div>

                <form method="POST" action="{{ route('admin.loans.store') }}" class="loan-form-grid mt-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                    @csrf
                    <div class="loan-field">
                        <label class="loan-label" for="loanBookId">Buku Yang Dipinjam</label>
                        <select id="loanBookId" name="book_id" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih buku</option>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}">{{ $book->title }} (stok {{ $book->stock_available }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="loanMemberId">Nama Peminjam</label>
                        <select id="loanMemberId" name="member_id" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih peminjam</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} - {{ $member->role?->label ?? 'Tanpa role' }}{{ $member->academicLabel() ? ' | '.$member->academicLabel() : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="member-form-grid">
                        <div class="loan-field">
                            <label class="loan-label" for="loanBorrowedAt">Tanggal Pinjam</label>
                            <input id="loanBorrowedAt" type="date" name="borrowed_at" class="form-input px-3 py-3 text-sm" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label" for="loanDueAt">Tanggal Kembali</label>
                            <input id="loanDueAt" type="date" name="due_at" class="form-input px-3 py-3 text-sm" value="{{ now()->addDay()->toDateString() }}" required readonly>
                        </div>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="loanNotes">Catatan</label>
                        <textarea id="loanNotes" name="notes" class="form-textarea px-3 py-3 text-sm" rows="4" placeholder="Catatan peminjaman"></textarea>
                    </div>
                    <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Peminjaman</button>
                </form>
            </div>

            <div class="loan-add">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="loan-add-title">Pengembalian Buku</div>
                        <div class="loan-help mt-2">Input pengembalian dan sistem akan cek apakah buku dikembalikan telat atau tidak.</div>
                    </div>
                    <div class="member-badge"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Return</div>
                </div>

                <form method="POST" action="{{ route('admin.loans.return') }}" class="loan-form-grid mt-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                    @csrf
                    <div class="loan-field">
                        <label class="loan-label" for="returnLoanId">Transaksi Peminjaman</label>
                        <select id="returnLoanId" name="loan_id" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih transaksi aktif</option>
                            @foreach ($activeLoans as $activeLoan)
                                <option value="{{ $activeLoan->id }}">
                                    {{ $activeLoan->member?->name ?? 'Member' }} - {{ $activeLoan->book?->title ?? 'Buku' }}{{ $activeLoan->member?->academicLabel() ? ' | '.$activeLoan->member->academicLabel() : '' }} | jatuh tempo {{ optional($activeLoan->due_at)->translatedFormat('d M Y') ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="loanReturnedAt">Tanggal Pengembalian</label>
                        <input id="loanReturnedAt" type="date" name="returned_at" class="form-input px-3 py-3 text-sm" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="loan-return-preview">
                        <div class="loan-return-preview-title">Cek keterlambatan</div>
                        <div class="loan-return-preview-sub">Jika tanggal pengembalian melewati tanggal kembali, sistem tetap menyimpan pengembalian dan menampilkan bahwa buku terlambat dikembalikan.</div>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="returnNotes">Catatan Pengembalian</label>
                        <textarea id="returnNotes" name="notes" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Catatan pengembalian"></textarea>
                    </div>
                    <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Pengembalian</button>
                </form>
            </div>

            <div class="loan-add">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="loan-add-title">Mengelola Sanksi</div>
                        <div class="loan-help mt-2">Kalau telat, hilang, atau rusak, petugas bisa memberi sanksi sebagai pengganti denda.</div>
                    </div>
                    <div class="member-badge"><i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Sanksi</div>
                </div>

                <form method="POST" action="{{ route('admin.loans.sanctions.store') }}" class="loan-form-grid mt-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                    @csrf
                    <div class="loan-field">
                        <label class="loan-label" for="sanctionLoanId">Pilih Transaksi</label>
                        <select id="sanctionLoanId" name="loan_id" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih transaksi</option>
                            @foreach ($sanctionableLoans as $loanOption)
                                <option value="{{ $loanOption->id }}">
                                    {{ $loanOption->member?->name ?? 'Member' }} - {{ $loanOption->book?->title ?? 'Buku' }}{{ $loanOption->member?->academicLabel() ? ' | '.$loanOption->member->academicLabel() : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="sanctionType">Jenis Sanksi</label>
                        <select id="sanctionType" name="type" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih jenis sanksi</option>
                            <option value="suspend_borrowing">Tidak boleh pinjam selama beberapa hari</option>
                            <option value="warning">Peringatan / warning</option>
                            <option value="replace_book">Harus mengganti buku hilang/rusak</option>
                        </select>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="sanctionReason">Alasan Sanksi</label>
                        <textarea id="sanctionReason" name="reason" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Contoh: telat 3 hari" required></textarea>
                    </div>
                    <div class="member-form-grid">
                        <div class="loan-field">
                            <label class="loan-label" for="sanctionDuration">Lama Sanksi (hari)</label>
                            <input id="sanctionDuration" type="number" min="0" max="365" name="duration_days" class="form-input px-3 py-3 text-sm" placeholder="Contoh 7">
                        </div>
                        <div class="loan-field">
                            <label class="loan-label" for="sanctionStartsAt">Tanggal Mulai</label>
                            <input id="sanctionStartsAt" type="date" name="starts_at" class="form-input px-3 py-3 text-sm" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="sanctionNotes">Catatan Tambahan</label>
                        <textarea id="sanctionNotes" name="notes" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Catatan tambahan sanksi"></textarea>
                    </div>
                    <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Sanksi</button>
                </form>

                <div class="loan-sanction-list">
                    @forelse ($sanctions as $sanction)
                        <div class="loan-sanction-item">
                            <div class="loan-sanction-type">
                                {{
                                    match ($sanction->type) {
                                        'suspend_borrowing' => 'Larangan Pinjam',
                                        'warning' => 'Warning',
                                        'replace_book' => 'Ganti Buku',
                                        default => $sanction->type,
                                    }
                                }}
                            </div>
                            <div class="loan-sanction-reason">{{ $sanction->reason }}</div>
                            <div class="loan-sanction-meta">
                                {{ $sanction->member?->name ?? 'Member' }}
                                @if ($sanction->loan?->book?->title)
                                    | {{ $sanction->loan->book->title }}
                                @endif
                                | Mulai {{ optional($sanction->starts_at)->translatedFormat('d M Y') ?? '-' }}
                                @if ($sanction->ends_at)
                                    | Sampai {{ optional($sanction->ends_at)->translatedFormat('d M Y') ?? '-' }}
                                @elseif ($sanction->duration_days !== null)
                                    | Durasi {{ $sanction->duration_days }} hari
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="loan-return-preview">
                            <div class="loan-return-preview-title">Belum ada sanksi</div>
                            <div class="loan-return-preview-sub">Sanksi terbaru akan muncul di sini setelah petugas menyimpannya.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="loan-add">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="loan-add-title">Update Status Peminjam</div>
                        <div class="loan-help mt-2">Tandai peminjam sebagai aktif atau disanksi. Jika disanksi, anggota tidak boleh pinjam sementara.</div>
                    </div>
                    <div class="member-badge"><i data-lucide="user-round-cog" class="w-3.5 h-3.5"></i> Status</div>
                </div>

                <form method="POST" action="{{ route('admin.loans.borrower-status.update') }}" class="loan-form-grid mt-5" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                    @csrf
                    <div class="loan-field">
                        <label class="loan-label" for="borrowerMemberId">Peminjam</label>
                        <select id="borrowerMemberId" name="member_id" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih peminjam</option>
                            @foreach ($memberStatuses as $memberStatus)
                                <option value="{{ $memberStatus->id }}">
                                    {{ $memberStatus->name }} - {{ $memberStatus->borrower_status === 'sanctioned' ? 'Disanksi' : 'Aktif' }}{{ $memberStatus->academicLabel() ? ' | '.$memberStatus->academicLabel() : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="borrowerStatus">Status Peminjam</label>
                        <select id="borrowerStatus" name="status" class="form-select px-3 py-3 text-sm" required>
                            <option value="">Pilih status</option>
                            <option value="active">Aktif (boleh pinjam)</option>
                            <option value="sanctioned">Disanksi (tidak boleh pinjam sementara)</option>
                        </select>
                    </div>
                    <div class="member-form-grid">
                        <div class="loan-field">
                            <label class="loan-label" for="borrowerDuration">Lama Sanksi (hari)</label>
                            <input id="borrowerDuration" type="number" min="0" max="365" name="duration_days" class="form-input px-3 py-3 text-sm" placeholder="Kosongkan jika tidak dibatasi hari">
                        </div>
                        <div class="loan-field">
                            <label class="loan-label" for="borrowerStartsAt">Tanggal Mulai</label>
                            <input id="borrowerStartsAt" type="date" name="starts_at" class="form-input px-3 py-3 text-sm" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="borrowerReason">Alasan</label>
                        <textarea id="borrowerReason" name="reason" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Contoh: telat 3 hari"></textarea>
                    </div>
                    <div class="loan-field">
                        <label class="loan-label" for="borrowerStatusNotes">Catatan Tambahan</label>
                        <textarea id="borrowerStatusNotes" name="notes" class="form-textarea px-3 py-3 text-sm" rows="3" placeholder="Catatan tambahan status peminjam"></textarea>
                    </div>
                    <button class="btn-primary rounded-xl px-4 py-3 text-sm font-semibold w-full">Simpan Status Peminjam</button>
                </form>
            </div>
        </div>

        <div class="loan-list">
            <div class="loan-list-head">
                <div class="loan-list-title">Daftar Peminjaman Buku</div>
                <div class="member-badge"><i data-lucide="history" class="w-3.5 h-3.5"></i> Semua transaksi</div>
            </div>

            @if ($loans->count())
                <div class="loan-table-wrap">
                    <table class="loan-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Keterlambatan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loans as $loan)
                                @php($daysLate = $loan->status === 'late' ? max((int) optional($loan->due_at)->diffInDays(now(), false) * -1, 0) : 0)
                                <tr>
                                    <td>
                                        <div class="loan-book">
                                            <div class="loan-cover">
                                                @if ($loan->book?->cover_image)
                                                    <img src="{{ asset('storage/'.$loan->book->cover_image) }}" alt="{{ $loan->book?->title }}" style="width:100%;height:100%;object-fit:cover;">
                                                @else
                                                    {{ strtoupper(substr($loan->book?->title ?? 'B', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="loan-book-title">{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</div>
                                                <div class="loan-book-sub">{{ $loan->book?->author ?? 'Penulis tidak tersedia' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="loan-member">
                                        <div class="loan-member-name">{{ $loan->member?->name ?? 'Peminjam tidak ditemukan' }}</div>
                                            <div class="loan-member-sub">{{ $loan->member?->role?->label ?? 'Tanpa role' }} | {{ $loan->member?->username ?? '-' }}</div>
                                            @if ($loan->member?->academicLabel())
                                                <div class="loan-member-sub">{{ $loan->member->academicLabel() }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="loan-date">{{ optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}</div>
                                        <span class="loan-date-sub">Kembali: {{ optional($loan->returned_at ?? $loan->due_at)->translatedFormat('d M Y') ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if ($loan->status === 'late')
                                            <span class="loan-late">{{ max(now()->diffInDays($loan->due_at, false) * -1, 0) }} hari</span>
                                        @else
                                            <span class="loan-safe">Aman</span>
                                        @endif
                                    </td>
                                    <td style="min-width:170px;">
                                        <form method="POST" action="{{ route('admin.loans.update', $loan) }}" class="space-y-2" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="loan-status" onchange="this.form.submit()">
                                                <option value="requested" @selected($loan->status === 'requested')>Menunggu</option>
                                                <option value="borrowed" @selected($loan->status === 'borrowed')>Dipinjam</option>
                                                <option value="late" @selected($loan->status === 'late')>Terlambat</option>
                                                <option value="returned" @selected($loan->status === 'returned')>Dikembalikan</option>
                                            </select>
                                            <input type="hidden" name="returned_at" value="{{ optional($loan->returned_at)->toDateString() }}">
                                            <input type="hidden" name="notes" value="{{ $loan->notes }}">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $loans->links() }}</div>
            @else
                <div class="loan-empty">
                    <div class="text-lg font-semibold text-slate2-900">Belum ada data peminjaman</div>
                    <div class="mt-2 text-sm">Semua data pinjam, peminjam, tanggal, dan keterlambatan akan muncul di sini.</div>
                </div>
            @endif

            <div class="loan-list-head mt-8">
                <div class="loan-list-title">Monitoring Sanksi Peminjam</div>
                <div class="member-badge"><i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Status sanksi</div>
            </div>

            <div class="loan-monitoring-list">
                @forelse ($sanctionMonitoring as $monitoring)
                    <div class="loan-monitoring-item">
                        <div class="loan-monitoring-head">
                            <div>
                                <div class="loan-monitoring-name">{{ $monitoring->member?->name ?? 'Peminjam tidak ditemukan' }}</div>
                                <div class="loan-monitoring-sub">
                                    {{ $monitoring->reason }}
                                    @if ($monitoring->member?->academicLabel())
                                        | {{ $monitoring->member->academicLabel() }}
                                    @endif
                                    @if ($monitoring->loan?->book?->title)
                                        | Buku: {{ $monitoring->loan->book->title }}
                                    @endif
                                    @if ($monitoring->ends_at)
                                        | Berakhir: {{ optional($monitoring->ends_at)->translatedFormat('d M Y') ?? '-' }}
                                    @elseif ($monitoring->duration_days !== null)
                                        | Durasi: {{ $monitoring->duration_days }} hari
                                    @endif
                                </div>
                            </div>
                            <div class="loan-monitoring-status {{ $monitoring->monitoring_state }}">
                                {{
                                    match ($monitoring->monitoring_state) {
                                        'active' => 'Disanksi',
                                        'expired' => 'Masa selesai',
                                        default => 'Aktif kembali',
                                    }
                                }}
                            </div>
                        </div>

                        @if ($monitoring->monitoring_state !== 'completed')
                            <form method="POST" action="{{ route('admin.loans.sanctions.update', $monitoring) }}" class="mt-3" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <button class="btn-soft rounded-xl px-4 py-2 text-xs font-semibold" type="submit">Tandai Aktif Kembali</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="loan-empty">
                        <div class="text-lg font-semibold text-slate2-900">Belum ada sanksi aktif</div>
                        <div class="mt-2 text-sm">Daftar peminjam yang kena sanksi dan masa berakhirnya akan muncul di sini.</div>
                    </div>
                @endforelse
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
