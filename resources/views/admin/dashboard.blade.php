@extends('layouts.admin')

@section('content')
@php($title = 'Dashboard')
@php($eyebrow = 'Ringkasan Sistem')

<div
    id="asyncDashboardWrap"
    class="dbx"
    data-dashboard-role="{{ $isPrincipalDashboard ? 'principal' : ($isBorrowerDashboard ? 'borrower' : 'other') }}"
    data-principal-signatures="{{ $isPrincipalDashboard ? $principalProcurements->map(fn ($procurement) => 'principal-procurement-'.$procurement->id.'-'.$procurement->status)->implode('|') : '' }}"
    data-borrower-signatures="{{ $isBorrowerDashboard ? $borrowerNotifications->pluck('signature')->filter()->implode('|') : '' }}"
    data-borrower-state-signature="{{ $isBorrowerDashboard ? ($borrowerSnapshot['signature'] ?? '') : '' }}"
>
    <div class="dbx-pattern"></div>
    <div class="dbx-body">
        <section class="dbx-welcome">
            <div>
                <div class="dbx-welcome-badge"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Dashboard</div>
                <div class="dbx-welcome-title">Hello, {{ auth()->user()?->name ?? 'Pengguna' }}</div>
                <div class="dbx-welcome-sub">
                    @if ($isBorrowerDashboard)
                        {{ $dashboardMeta['today_label'] }}. Berikut daftar buku yang saat ini tersedia untuk dipinjam.
                    @else
                        {{ $dashboardMeta['today_label'] }}. Semoga aktivitas perpustakaan hari ini lancar.
                    @endif
                </div>
            </div>
        </section>

        @if ($isBorrowerDashboard)
            <section class="dbx-borrower-stats">
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-requested">{{ $borrowerLoanStats['requested'] }}</div>
                    <div class="dbx-borrower-stat-label">Pengajuan menunggu petugas</div>
                </article>
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-borrowed">{{ $borrowerLoanStats['borrowed'] }}</div>
                    <div class="dbx-borrower-stat-label">Buku sedang dipinjam</div>
                </article>
                <article class="dbx-borrower-stat">
                    <div class="dbx-borrower-stat-value" id="stat-returned">{{ $borrowerLoanStats['returned'] }}</div>
                    <div class="dbx-borrower-stat-label">Riwayat selesai</div>
                </article>
            </section>

            <section class="dbx-borrower-profile">
                <article class="dbx-borrower-panel">
                    <div class="dbx-borrower-panel-title">Identitas Peminjam</div>
                    <div class="dbx-borrower-panel-value">{{ auth()->user()?->name ?? 'Peminjam' }}</div>
                    <div class="dbx-borrower-panel-sub">
                        {{ auth()->user()?->role?->label ?? 'Anggota' }}
                        @if (auth()->user()?->academicLabel())
                            | {{ auth()->user()->academicLabel() }}
                        @endif
                    </div>
                    <div class="dbx-borrower-panel-sub" id="borrowerAccountStatus">
                        Status akun:
                        <strong>{{ $borrowerActiveSanction ? 'Sedang kena sanksi' : 'Aktif' }}</strong>
                    </div>
                </article>
            </section>

            @if ($borrowerActiveSanction)
                <div class="dbx-borrower-alert" id="borrowerSanctionAlert">
                    Akun Anda sedang disanksi dan belum bisa mengajukan pinjam.
                    @if ($borrowerActiveSanction->ends_at)
                        Masa sanksi sampai {{ $borrowerActiveSanction->ends_at->translatedFormat('d M Y') }}.
                    @endif
                    Anda harus menunggu sampai masa sanksi selesai sebelum bisa pinjam lagi.
                </div>
            @else
                <div class="dbx-borrower-alert" id="borrowerSanctionAlert" style="display:none;"></div>
            @endif

            @if ($borrowerNotifications->isNotEmpty())
                <section class="dbx-notif-list" id="borrowerNotificationList">
                    @foreach ($borrowerNotifications as $notification)
                        <article class="dbx-notif-item {{ $notification['tone'] }}" data-signature="{{ $notification['signature'] }}">
                            <div class="dbx-notif-title">{{ $notification['title'] }}</div>
                            <div class="dbx-notif-body">{{ $notification['body'] }}</div>
                        </article>
                    @endforeach
                </section>
            @else
                <section class="dbx-notif-list" id="borrowerNotificationList" style="display:none;"></section>
            @endif

            <section class="dbx-card">
                <div class="dbx-card-header">
                    <h3 class="dbx-card-title">Cari, Lihat, dan Ajukan Pinjam Buku</h3>
                </div>
                <div class="dbx-card-body">
                    @error('loan_request')
                        <div class="dbx-borrower-alert" style="margin-bottom:18px;">{{ $message }}</div>
                    @enderror

                    <form method="GET" action="{{ route('dashboard') }}" class="dbx-book-filters" id="borrowerBookFilterForm" data-async="true" data-refresh-targets="#asyncDashboardWrap" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                        <input
                            type="text"
                            name="q"
                            id="borrowerBookKeyword"
                            class="dbx-book-filter-field"
                            style="flex:1 1 260px;min-width:220px;"
                            value="{{ $bookFilters['keyword'] }}"
                            placeholder="Cari judul atau penulis"
                            autocomplete="off"
                        >
                        <select name="category" id="borrowerBookCategoryFilter" class="dbx-book-filter-field" style="flex:0 1 180px;min-width:160px;">
                            style="flex:0 1 180px;min-width:160px;"
                            <option value="">Semua kategori</option>
                            @foreach ($borrowerCategories as $category)
                                <option value="{{ $category->slug }}" @selected($bookFilters['category'] === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select name="availability" id="borrowerBookAvailabilityFilter" class="dbx-book-filter-field" style="flex:0 0 140px;min-width:140px;">
                            <option value="available" @selected($bookFilters['availability'] === 'available')>Tersedia</option>
                            <option value="all" @selected($bookFilters['availability'] === 'all')>Semua buku</option>
                        </select>
                        <div style="position:relative;display:inline-flex;align-items:center;">
                            <button type="button" id="borrowerImageSearchSourceBtn" class="dbx-book-filter-btn" style="width:48px;height:48px;padding:0;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;" aria-label="Pilih foto buku">
                                <i data-lucide="camera" class="w-5 h-5"></i>
                            </button>
                            <div id="borrowerImageSearchSourceMenu" style="display:none;position:absolute;top:58px;right:0;z-index:20;min-width:210px;padding:10px;border:1px solid rgba(196,149,106,.18);border-radius:16px;background:#fff;box-shadow:0 16px 30px rgba(15,76,92,.12);">
                                <button type="button" id="borrowerImageSearchCamera" class="dbx-book-filter-btn" style="width:100%;justify-content:flex-start;margin-bottom:8px;">Foto Langsung</button>
                                <button type="button" id="borrowerImageSearchGallery" class="dbx-book-filter-btn" style="width:100%;justify-content:flex-start;">Dari File / Galeri</button>
                            </div>
                        </div>
                        <button type="submit" class="dbx-book-filter-btn" style="min-width:96px;height:48px;padding:0 14px;white-space:nowrap;"><i data-lucide="search" class="w-4 h-4"></i>Cari</button>
                    </form>

                    <input type="file" id="borrowerImageSearchCameraInput" name="image" accept="image/*" capture="environment" style="display:none;">
                    <input type="file" id="borrowerImageSearchGalleryInput" name="image" accept="image/*" style="display:none;">
                    <div id="borrowerImageSearchPreviewWrap" style="display:none;align-items:center;justify-content:center;min-height:140px;margin-top:14px;border:1px dashed rgba(196,149,106,.22);border-radius:18px;background:#fff;overflow:hidden;">
                        <img id="borrowerImageSearchPreview" alt="Preview foto buku" style="max-width:100%;max-height:220px;object-fit:contain;">
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                        <div id="borrowerImageSearchFileName" style="font-size:13px;color:var(--muted);display:none;"></div>
                        <div id="borrowerImageSearchActions" style="display:none;gap:8px;">
                            <button type="button" id="borrowerImageSearchActionBtn" class="dbx-book-filter-btn" style="height:36px;padding:0 14px;white-space:nowrap;font-size:13px;"><i data-lucide="search" class="w-4 h-4"></i>Cari Buku</button>
                            <button type="button" id="borrowerImageSearchClearBtn" class="dbx-book-filter-btn" style="height:36px;padding:0 14px;white-space:nowrap;font-size:13px;background:var(--red-light);color:var(--red);border-color:transparent;"><i data-lucide="trash-2" class="w-4 h-4"></i>Hapus</button>
                        </div>
                    </div>
                    <div id="borrowerImageSearchStatus" style="font-size:13px;color:var(--muted);margin-top:8px;"></div>
                    <div id="borrowerImageSearchResults" style="display:none;margin-top:16px;"></div>

                    <div class="dbx-book-section-head">
                        <div class="dbx-book-section-copy">
                            <div class="dbx-card-title">Koleksi Pilihan</div>
                            <div class="dbx-book-section-sub">Tampilan dibuat seperti etalase buku: cover lebih dominan, info singkat di bawah, lalu klik satu buku untuk buka panel pinjam dari samping.</div>
                        </div>
                        <div class="dbx-book-section-badge">
                            <i data-lucide="panel-right-open" class="w-4 h-4"></i>
                            Klik cover untuk pinjam
                        </div>
                    </div>

                    <div class="dbx-book-showcase">
                        <div class="dbx-book-grid-wrap">
                            <div class="dbx-book-grid" id="borrowerBookGrid">
                                @forelse ($borrowerBooks as $book)
                                    <article
                                        class="dbx-book-card js-borrow-book"
                                        role="button"
                                        tabindex="0"
                                        data-id="{{ $book->id }}"
                                        data-title="{{ $book->title }}"
                                        data-author="{{ $book->author ?? 'Penulis tidak tersedia' }}"
                                        data-category="{{ $book->category?->name ?? 'Tanpa kategori' }}"
                                        data-stock="{{ $book->stock_available }}"
                                        data-cover-url="{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}"
                                        data-borrowed-at="{{ now()->toDateString() }}"
                                        data-due-at="{{ now()->addDay()->toDateString() }}"
                                        data-borrow-state="{{ $book->borrow_state ?? ($borrowerActiveSanction ? 'sanctioned' : ($book->stock_available > 0 ? 'available' : 'unavailable')) }}"
                                        data-can-borrow="{{ ($book->can_borrow ?? ($book->stock_available > 0 && ! $borrowerActiveSanction)) ? '1' : '0' }}"
                                    >
                                        <div class="dbx-book-thumb">
                                            @if ($book->cover_image)
                                                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}">
                                            @else
                                                <div class="dbx-book-fallback">{{ strtoupper(substr($book->title, 0, 1)) }}</div>
                                            @endif
                                        </div>
                                        <div class="dbx-book-body">
                                            <div class="dbx-book-chip {{ ($book->borrow_state ?? '') !== 'available' ? 'unavailable' : '' }}">
                                                {{
                                                    ($book->borrow_state ?? null) === 'requested'
                                                        ? 'Menunggu petugas'
                                                        : (($book->borrow_state ?? null) === 'borrowed'
                                                            ? 'Sedang dipinjam'
                                                        : (($book->borrow_state ?? null) === 'sanctioned'
                                                            ? 'Akun disanksi'
                                                            : (($book->borrow_state ?? null) === 'available' ? 'Tersedia' : 'Habis')))
                                                }}
                                            </div>
                                            <div class="dbx-book-name">{{ $book->title }}</div>
                                            <div class="dbx-book-author">{{ $book->author ?? 'Penulis tidak tersedia' }}</div>
                                            <div class="dbx-book-meta">
                                                <span class="dbx-book-stock">{{ $book->stock_available }} stok</span>
                                                <span>{{ $book->category?->name ?? 'Umum' }}</span>
                                            </div>
                                            <div class="dbx-book-open">
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                Lihat
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="text-sm text-slate2-400" id="borrowerBookEmpty">Buku yang dicari belum ditemukan.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        @elseif ($isPrincipalDashboard)
        <section class="dbx-stats">
            <article class="dbx-stat members">
                <div class="dbx-stat-icon"><i data-lucide="users"></i></div>
                <div class="dbx-stat-value">{{ number_format($principalMetrics['petugas_active_today']) }}</div>
                <div class="dbx-stat-label">Petugas Aktif Hari Ini</div>
                <div class="dbx-stat-trend up"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i>{{ $principalMetrics['petugas_actions_today'] }} aktivitas tercatat</div>
            </article>
            <article class="dbx-stat books">
                <div class="dbx-stat-icon"><i data-lucide="library-big"></i></div>
                <div class="dbx-stat-value">{{ number_format($principalMetrics['books_growth']) }}</div>
                <div class="dbx-stat-label">Buku Baru 30 Hari</div>
                <div class="dbx-stat-trend up"><i data-lucide="book-plus" class="w-3.5 h-3.5"></i>Perkembangan koleksi perpustakaan</div>
            </article>
            <article class="dbx-stat borrowed">
                <div class="dbx-stat-icon"><i data-lucide="book-up-2"></i></div>
                <div class="dbx-stat-value">{{ number_format($principalMetrics['loans_growth']) }}</div>
                <div class="dbx-stat-label">Transaksi 30 Hari</div>
                <div class="dbx-stat-trend up"><i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>Aktivitas layanan perpustakaan</div>
            </article>
            <article class="dbx-stat overdue">
                <div class="dbx-stat-icon"><i data-lucide="activity"></i></div>
                <div class="dbx-stat-value">{{ number_format($principalMetrics['service_score']) }}</div>
                <div class="dbx-stat-label">Skor Layanan</div>
                <div class="dbx-stat-trend {{ $principalMetrics['service_score'] >= 80 ? 'up' : 'down' }}"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i>Cek apakah layanan berjalan baik</div>
            </article>
        </section>

        <section class="dbx-content">
            <article class="dbx-card">
                <div class="dbx-card-header">
                    <h3 class="dbx-card-title">Monitoring Kinerja</h3>
                </div>
                <div class="dbx-card-body">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Melihat aktivitas petugas</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900">{{ number_format($principalMetrics['petugas_actions_today']) }}</div>
                            <div class="mt-2 text-sm text-slate2-600">Aktivitas petugas hari ini tercatat dari setiap aksi input, update, dan proses layanan.</div>
                        </div>
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Melihat perkembangan perpustakaan</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900">{{ number_format($principalMetrics['books_growth']) }}</div>
                            <div class="mt-2 text-sm text-slate2-600">Penambahan koleksi dalam 30 hari terakhir dan {{ number_format($principalMetrics['loans_growth']) }} transaksi layanan berjalan.</div>
                        </div>
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                            <div class="text-sm font-semibold text-slate2-900">Cek apakah layanan berjalan baik</div>
                            <div class="mt-2 text-3xl font-bold text-slate2-900">{{ number_format($principalMetrics['returned_today']) }}</div>
                            <div class="mt-2 text-sm text-slate2-600">Buku kembali hari ini {{ $principalMetrics['returned_today'] }}, permintaan menunggu {{ $principalMetrics['pending_requests'] }}, terlambat {{ $principalMetrics['late_loans'] }}.</div>
                        </div>
                    </div>
                </div>
            </article>

            <div class="dbx-side">
                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Aktivitas Petugas</h3>
                    </div>
                    <div class="dbx-card-body">
                        @forelse ($principalActivityLogs as $activity)
                            <div class="dbx-activity-item">
                                <div class="dbx-activity-icon"><i data-lucide="briefcase-business"></i></div>
                                <div class="dbx-activity-content">
                                    <h4>{{ $activity->user?->name ?? 'Petugas' }}</h4>
                                    <p>{{ $activity->description }}</p>
                                    <div class="dbx-activity-meta">
                                        <span class="dbx-activity-badge update">{{ ucfirst($activity->action) }}</span>
                                        <span class="dbx-activity-module">{{ str_replace('_', ' ', $activity->module) }}</span>
                                    </div>
                                    <span>{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate2-400">Belum ada aktivitas petugas yang tercatat hari ini.</p>
                        @endforelse
                    </div>
                </article>

                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Kondisi Layanan</h3>
                    </div>
                    <div class="dbx-card-body">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                                <div class="text-sm font-semibold text-slate2-900">Permintaan Menunggu</div>
                                <div class="mt-2 text-2xl font-bold text-slate2-900">{{ $principalMetrics['pending_requests'] }}</div>
                                <div class="mt-2 text-sm text-slate2-600">Semakin kecil angka ini, semakin cepat layanan petugas diproses.</div>
                            </div>
                            <div class="rounded-2xl border border-slate2-100 bg-white p-4">
                                <div class="text-sm font-semibold text-slate2-900">Keterlambatan Aktif</div>
                                <div class="mt-2 text-2xl font-bold text-slate2-900">{{ $principalMetrics['late_loans'] }}</div>
                                <div class="mt-2 text-sm text-slate2-600">Dipakai untuk melihat apakah layanan pengembalian dan pengawasan berjalan baik.</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Persetujuan Pengadaan Buku</h3>
                    </div>
                    <div class="dbx-card-body">
                        <div class="rounded-2xl border border-slate2-100 bg-white p-4 mb-4">
                            <div class="text-sm font-semibold text-slate2-900">Usulan Menunggu</div>
                            <div class="mt-2 text-2xl font-bold text-slate2-900">{{ $principalMetrics['pending_procurements'] }}</div>
                            <div class="mt-2 text-sm text-slate2-600">Melihat usulan buku baru dan menyetujui pembelian atau penambahan koleksi.</div>
                        </div>

                        <div class="space-y-4">
                            @forelse ($principalProcurements as $procurement)
                                <div class="rounded-2xl border border-slate2-100 bg-white p-4 js-principal-procurement-card">
                                    <div class="text-sm font-semibold text-slate2-900">{{ $procurement->title }}</div>
                                    <div class="mt-1 text-sm text-slate2-600">{{ $procurement->author }} | Jumlah {{ $procurement->quantity }}</div>
                                    <div class="mt-2 text-sm text-slate2-600">
                                        Pengusul {{ $procurement->proposer?->name ?? 'Petugas' }}
                                        @if ($procurement->category?->name)
                                            | {{ $procurement->category->name }}
                                        @endif
                                        @if ($procurement->notes)
                                            | {{ $procurement->notes }}
                                        @endif
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.books.procurements.approve', $procurement) }}" data-async="true" data-remove-closest=".js-principal-procurement-card" data-refresh-targets="#asyncDashboardWrap">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn-primary rounded-xl px-4 py-2 text-sm font-semibold" type="submit">Setujui Pengadaan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.books.procurements.reject', $procurement) }}" data-async="true" data-remove-closest=".js-principal-procurement-card" data-refresh-targets="#asyncDashboardWrap">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn-soft rounded-xl px-4 py-2 text-sm font-semibold" type="submit">Tolak</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate2-400">Belum ada usulan buku baru yang menunggu persetujuan.</p>
                            @endforelse
                        </div>
                    </div>
                </article>


            </div>
        </section>
        @else
        <section class="dbx-stats">
            <article class="dbx-stat books">
                <div class="dbx-stat-icon"><i data-lucide="book-open"></i></div>
                <div class="dbx-stat-value">{{ number_format($stats['books']) }}</div>
                <div class="dbx-stat-label">Total Koleksi Buku</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Data katalog aktif</div>
            </article>
            <article class="dbx-stat members">
                <div class="dbx-stat-icon"><i data-lucide="users"></i></div>
                <div class="dbx-stat-value">{{ number_format($stats['members']) }}</div>
                <div class="dbx-stat-label">Anggota Aktif</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Role peminjam aktif</div>
            </article>
            <article class="dbx-stat borrowed">
                <div class="dbx-stat-icon"><i data-lucide="book-up-2"></i></div>
                <div class="dbx-stat-value">{{ number_format($stats['borrowed']) }}</div>
                <div class="dbx-stat-label">Sedang Dipinjam</div>
                <div class="dbx-stat-trend up"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Transaksi berjalan</div>
            </article>
            <article class="dbx-stat overdue">
                <div class="dbx-stat-icon"><i data-lucide="triangle-alert"></i></div>
                <div class="dbx-stat-value">{{ number_format($stats['late']) }}</div>
                <div class="dbx-stat-label">Terlambat Kembali</div>
                <div class="dbx-stat-trend down"><i data-lucide="trending-down" class="w-3.5 h-3.5"></i>Perlu tindak lanjut</div>
            </article>
        </section>

        <section class="dbx-content">
            <article class="dbx-card">
                <div class="dbx-card-header">
                    <h3 class="dbx-card-title">Peminjaman Terbaru</h3>
                    <a href="{{ route('admin.loans.index') }}" class="dbx-card-action">Lihat Semua</a>
                </div>
                <div class="dbx-table-wrap">
                    <table class="dbx-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLoans as $loan)
                                @php($status = $loan->status === 'late' ? 'overdue' : ($loan->status === 'returned' ? 'pending' : 'active'))
                                @php($memberName = $loan->member?->name ?? 'Anggota tidak ditemukan')
                                <tr>
                                    <td>
                                        <div class="dbx-book-info">
                                            <div class="dbx-book-cover">
                                                @if ($loan->book?->cover_image)
                                                    <img src="{{ asset('storage/'.$loan->book->cover_image) }}" alt="{{ $loan->book?->title }}" style="width:100%;height:100%;object-fit:cover;">
                                                @else
                                                    {{ strtoupper(substr($loan->book?->title ?? 'B', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="dbx-book-details">
                                                <h4>{{ $loan->book?->title ?? 'Buku tidak ditemukan' }}</h4>
                                                <span>{{ $loan->book?->author ?? 'Penulis tidak tersedia' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dbx-member-info">
                                            <div class="dbx-member-avatar">{{ strtoupper(substr($memberName, 0, 2)) }}</div>
                                            <div>
                                                <div class="dbx-member-name">{{ $memberName }}</div>
                                                <div class="dbx-member-meta">
                                                    {{ $loan->member?->academicLabel() ?: ($loan->member?->email ?? 'Tanpa email') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>
                                        <span class="dbx-status {{ $status }}">
                                            {{ $loan->status === 'late' ? 'Terlambat' : ($loan->status === 'returned' ? 'Dikembalikan' : 'Dipinjam') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate2-400">Belum ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="dbx-side">
                <article class="dbx-card">
                    <div class="dbx-card-header">
                        <h3 class="dbx-card-title">Buku Terpopuler</h3>
                        <a href="{{ route('admin.books.index') }}" class="dbx-card-action">Lihat Semua</a>
                    </div>
                    <div class="dbx-card-body">
                        @forelse ($popularBooks as $index => $book)
                            <div class="dbx-popular-item">
                                <div class="dbx-rank {{ $index < 3 ? 'top' : '' }}">{{ $index + 1 }}</div>
                                <div class="dbx-popular-cover" style="background:linear-gradient(135deg,hsl({{ 180 + ($index * 20) }},40%,35%),hsl({{ 180 + ($index * 20) }},40%,45%));">
                                    @if ($book->cover_image)
                                        <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}" style="width:100%;height:100%;object-fit:cover;">
                                    @endif
                                </div>
                                <div class="dbx-popular-info">
                                    <h4>{{ $book->title }}</h4>
                                    <span>{{ $book->author ?? 'Penulis tidak tersedia' }}</span>
                                </div>
                                <div class="dbx-borrow-count">{{ number_format($book->loans_count) }}x</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate2-400">Belum ada buku populer. Data akan muncul setelah ada peminjaman.</p>
                        @endforelse
                    </div>
                </article>

                @if ($canViewActivityLog)
                    <article class="dbx-card">
                        <div class="dbx-card-header">
                            <h3 class="dbx-card-title">Aktivitas Terbaru</h3>
                        </div>
                        <div class="dbx-card-body">
                            @forelse ($recentActivities as $activity)
                                @php($actionLabels = ['create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus'])
                                @php($actionIcons = ['create' => 'plus', 'update' => 'pencil', 'delete' => 'trash-2'])
                                <div class="dbx-activity-item">
                                    <div class="dbx-activity-icon"><i data-lucide="{{ $actionIcons[$activity->action] ?? 'history' }}"></i></div>
                                    <div class="dbx-activity-content">
                                        <h4>{{ $activity->user?->name ?? 'Sistem' }}</h4>
                                        <p>{{ $activity->description }}</p>
                                        <div class="dbx-activity-meta">
                                            <span class="dbx-activity-badge {{ $activity->action }}">{{ $actionLabels[$activity->action] ?? ucfirst($activity->action) }}</span>
                                            <span class="dbx-activity-module">{{ str_replace('_', ' ', $activity->module) }}</span>
                                        </div>
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate2-400">Belum ada aktivitas super admin seperti tambah, ubah, atau hapus data.</p>
                            @endforelse
                        </div>
                    </article>
                @endif

                @if ($isSuperAdminDashboard)
                    <article class="dbx-card">
                        <div class="dbx-card-header">
                            <h3 class="dbx-card-title">Hasil Pengadaan Buku</h3>
                        </div>
                        <div class="dbx-card-body">
                            @forelse ($superAdminProcurementUpdates as $procurement)
                                @php($isRejected = $procurement->status === 'rejected')
                                <div class="dbx-activity-item">
                                    <div class="dbx-activity-icon">
                                        <i data-lucide="{{ $isRejected ? 'circle-x' : 'badge-check' }}"></i>
                                    </div>
                                    <div class="dbx-activity-content">
                                        <h4>{{ $procurement->title }}</h4>
                                        <p>
                                            Usulan dari {{ $procurement->proposer?->name ?? 'Petugas' }}
                                            {{ $isRejected ? 'ditolak' : 'disetujui' }}
                                            oleh {{ $isRejected ? ($procurement->rejector?->name ?? 'Pemeriksa') : ($procurement->approver?->name ?? 'Pemeriksa') }}.
                                        </p>
                                        <div class="dbx-activity-meta">
                                            <span class="dbx-activity-badge {{ $isRejected ? 'delete' : 'create' }}">{{ $isRejected ? 'Ditolak' : 'Disetujui' }}</span>
                                            <span class="dbx-activity-module">{{ $procurement->category?->name ?? 'Tanpa kategori' }}</span>
                                        </div>
                                        <span>{{ optional($isRejected ? $procurement->rejected_at : $procurement->approved_at)?->diffForHumans() ?? $procurement->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate2-400">Belum ada hasil pengadaan buku yang diproses.</p>
                            @endforelse
                        </div>
                    </article>
                @endif
            </div>
        </section>
        @endif
    </div>
</div>

@if ($isBorrowerDashboard)
    <div id="borrowDrawerMask" class="dbx-drawer-mask"></div>

    <aside id="borrowDrawer" class="dbx-drawer" aria-hidden="true">
        <div class="dbx-drawer-head">
            <div>
                <div class="dbx-drawer-title">Ajukan Pinjam</div>
                <div class="dbx-drawer-sub">Pilih buku dari daftar. Detail buku dan form pengajuan akan tampil di panel samping ini.</div>
            </div>
            <button type="button" class="dbx-drawer-close" id="borrowDrawerClose">X</button>
        </div>
        <div class="dbx-drawer-body">
            <div id="borrowDrawerEmpty" class="dbx-empty-drawer">
                Klik salah satu buku untuk mulai ajukan pinjam.
            </div>

            <div id="borrowDrawerContent" style="display:none;">
                <div class="dbx-drawer-book">
                    <div class="dbx-drawer-thumb" id="borrowDrawerThumb">
                        <div class="dbx-drawer-fallback" id="borrowDrawerFallback">B</div>
                        <img id="borrowDrawerImage" src="" alt="" style="display:none;">
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div class="dbx-book-chip" id="borrowDrawerStatus">Tersedia</div>
                        <div class="dbx-drawer-book-title" id="borrowDrawerTitle">Judul buku</div>
                        <div class="dbx-drawer-book-author" id="borrowDrawerAuthor">Penulis</div>
                        <div class="dbx-drawer-book-meta">
                            <div class="dbx-drawer-book-box">
                                <div class="dbx-drawer-book-label">Stok</div>
                                <div class="dbx-drawer-book-value" id="borrowDrawerStock">0</div>
                            </div>
                            <div class="dbx-drawer-book-box">
                                <div class="dbx-drawer-book-label">Kategori</div>
                                <div class="dbx-drawer-book-value" id="borrowDrawerCategory">Tanpa kategori</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dbx-drawer-alert">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>PENTING: Batas waktu peminjaman buku ini adalah <strong>1 hari</strong> saja.</span>
                </div>

                <form method="POST" action="{{ route('loan-requests.store') }}" class="dbx-book-form" id="borrowerLoanForm">
                    @csrf
                    <input type="hidden" name="book_id" id="borrowDrawerBookId">
                    <div class="dbx-book-form-grid">
                        <input type="date" name="borrowed_at" id="borrowDrawerBorrowedAt" class="dbx-book-filter-field" value="{{ now()->toDateString() }}" required>
                        <input type="date" name="due_at" id="borrowDrawerDueAt" class="dbx-book-filter-field" value="{{ now()->addDay()->toDateString() }}" required readonly>
                    </div>
                    <textarea name="notes" class="dbx-book-note" placeholder="Catatan untuk petugas, misalnya ingin ambil langsung di perpustakaan."></textarea>
                    <button type="submit" class="dbx-book-submit" id="borrowDrawerSubmit">
                        <i data-lucide="book-plus" class="w-4 h-4"></i>
                        <span id="borrowDrawerSubmitLabel">Ajukan Pinjam Lewat Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const borrowDrawer = document.getElementById('borrowDrawer');
        const borrowDrawerMask = document.getElementById('borrowDrawerMask');
        function getBorrowerNotifList() {
            return document.getElementById('borrowerNotificationList');
        }

        function getSanctionAlert() {
            return document.getElementById('borrowerSanctionAlert');
        }

        function getAccountStatus() {
            return document.getElementById('borrowerAccountStatus');
        }

        function getBorrowerBookGrid() {
            return document.getElementById('borrowerBookGrid');
        }

        function getBorrowerBookKeyword() {
            return document.getElementById('borrowerBookKeyword');
        }

        function getBorrowerBookCategoryFilter() {
            return document.getElementById('borrowerBookCategoryFilter');
        }

        function getBorrowerBookAvailabilityFilter() {
            return document.getElementById('borrowerBookAvailabilityFilter');
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getBorrowStateLabel(borrowState) {
            if (borrowState === 'requested') return 'Menunggu petugas';
            if (borrowState === 'borrowed') return 'Sedang dipinjam';
            if (borrowState === 'sanctioned') return 'Akun disanksi';
            if (borrowState === 'available') return 'Tersedia';
            return 'Habis';
        }

        function getBorrowDrawerLabel(borrowState, isAvailable) {
            if (borrowState === 'sanctioned') return 'Pinjam Dinonaktifkan Sementara';
            if (borrowState === 'requested') return 'Pengajuan Sudah Dikirim';
            if (borrowState === 'borrowed') return 'Buku Sedang Dipinjam';
            return isAvailable ? 'Ajukan Pinjam Lewat Sistem' : 'Stok Tidak Tersedia';
        }

        function setBorrowerRealtimePause(isPaused) {
            window.__suspendGlobalPolling = isPaused;
        }

        function showToast(notification) {
            const toastWrap = document.getElementById('borrowerToastWrap');
            if (!toastWrap || !notification) return;

            const toast = document.createElement('div');
            toast.className = 'dbx-toast ' + (notification.tone || 'info');
            toast.innerHTML = '<div class="dbx-toast-title">' + escapeHtml(notification.title || 'Notifikasi baru') + '</div>'
                + '<div class="dbx-toast-body">' + escapeHtml(notification.body || '') + '</div>';
            toastWrap.appendChild(toast);

            requestAnimationFrame(function () {
                toast.classList.add('show');
            });

            window.setTimeout(function () {
                toast.classList.remove('show');
                window.setTimeout(function () {
                    toast.remove();
                }, 300);
            }, 4500);
        }

        function bindBorrowBookCards() {
            document.querySelectorAll('.js-borrow-book').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }

                button.dataset.bound = '1';
                button.addEventListener('click', function () {
                    openBorrowDrawer(button);
                    if (typeof showToast === 'function') {
                        showToast({
                            title: 'Info Batas Waktu',
                            body: 'Peminjaman buku ini dibatasi maksimal 1 hari.',
                            tone: 'info'
                        });
                    }
                });

                button.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openBorrowDrawer(button);
                    }
                });
            });
        }

        function applyBorrowerLoanRequestOptimistic(bookId) {
            if (!bookId) {
                return;
            }

            const card = document.querySelector('.js-borrow-book[data-id="' + CSS.escape(String(bookId)) + '"]');

            if (!card) {
                return;
            }

            card.dataset.borrowState = 'requested';
            card.dataset.canBorrow = '0';

            const chip = card.querySelector('.dbx-book-chip');
            if (chip) {
                chip.textContent = 'Menunggu petugas';
                chip.classList.add('unavailable');
            }

            const requestedStat = document.getElementById('stat-requested');
            if (requestedStat) {
                const currentValue = Number(requestedStat.textContent || 0);
                requestedStat.textContent = String(currentValue + 1);
            }
        }

        function renderBorrowerBooks(books) {
            const borrowerBookGrid = getBorrowerBookGrid();

            if (!borrowerBookGrid) {
                return;
            }

            if (!Array.isArray(books) || books.length === 0) {
                borrowerBookGrid.innerHTML = '<div class="text-sm text-slate2-400" id="borrowerBookEmpty">Buku yang dicari belum ditemukan.</div>';
                return;
            }

            borrowerBookGrid.innerHTML = books.map(function (book) {
                const chipClass = book.borrow_state === 'available' ? '' : ' unavailable';
                const chipLabel = getBorrowStateLabel(book.borrow_state);
                const imageHtml = book.cover_url
                    ? '<img src="' + escapeHtml(book.cover_url) + '" alt="' + escapeHtml(book.title) + '">'
                    : '<div class="dbx-book-fallback">' + escapeHtml((book.title || 'B').trim().charAt(0).toUpperCase()) + '</div>';

                return '<article class="dbx-book-card js-borrow-book"'
                    + ' role="button" tabindex="0"'
                    + ' data-id="' + escapeHtml(book.id) + '"'
                    + ' data-title="' + escapeHtml(book.title) + '"'
                    + ' data-author="' + escapeHtml(book.author || 'Penulis tidak tersedia') + '"'
                    + ' data-category="' + escapeHtml(book.category || 'Tanpa kategori') + '"'
                    + ' data-stock="' + escapeHtml(book.stock) + '"'
                    + ' data-cover-url="' + escapeHtml(book.cover_url || '') + '"'
                    + ' data-borrowed-at="' + escapeHtml(book.borrowed_at) + '"'
                    + ' data-due-at="' + escapeHtml(book.due_at) + '"'
                    + ' data-borrow-state="' + escapeHtml(book.borrow_state) + '"'
                    + ' data-can-borrow="' + (book.can_borrow ? '1' : '0') + '">'
                    + '<div class="dbx-book-thumb">' + imageHtml + '</div>'
                    + '<div class="dbx-book-body">'
                    + '<div class="dbx-book-chip' + chipClass + '">' + chipLabel + '</div>'
                    + '<div class="dbx-book-name">' + escapeHtml(book.title) + '</div>'
                    + '<div class="dbx-book-author">' + escapeHtml(book.author || 'Penulis tidak tersedia') + '</div>'
                    + '<div class="dbx-book-meta"><span class="dbx-book-stock">' + escapeHtml(book.stock) + ' stok</span><span>' + escapeHtml(book.category || 'Umum') + '</span></div>'
                    + '<div class="dbx-book-open"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>Lihat</div>'
                    + '</div></article>';
            }).join('');

            if (window.lucide) {
                window.lucide.createIcons();
            }

            bindBorrowBookCards();
        }

        function getBorrowerImageSearchCameraInput() {
            return document.getElementById('borrowerImageSearchCameraInput');
        }

        function getBorrowerImageSearchGalleryInput() {
            return document.getElementById('borrowerImageSearchGalleryInput');
        }

        function getBorrowerImageSearchPreviewWrap() {
            return document.getElementById('borrowerImageSearchPreviewWrap');
        }

        function getBorrowerImageSearchPreview() {
            return document.getElementById('borrowerImageSearchPreview');
        }

        function getBorrowerImageSearchFileName() {
            return document.getElementById('borrowerImageSearchFileName');
        }

        function getBorrowerImageSearchStatus() {
            return document.getElementById('borrowerImageSearchStatus');
        }

        function getBorrowerImageSearchResults() {
            return document.getElementById('borrowerImageSearchResults');
        }

        function setBorrowerImageSearchPreview(file) {
            const previewWrap = getBorrowerImageSearchPreviewWrap();
            const preview = getBorrowerImageSearchPreview();
            const fileName = getBorrowerImageSearchFileName();
            const actions = document.getElementById('borrowerImageSearchActions');

            if (!previewWrap || !preview || !fileName) {
                return;
            }

            if (!file) {
                previewWrap.style.display = 'none';
                preview.removeAttribute('src');
                fileName.style.display = 'none';
                fileName.textContent = '';
                if (actions) actions.style.display = 'none';
                return;
            }

            preview.src = URL.createObjectURL(file);
            previewWrap.style.display = 'flex';
            fileName.style.display = 'block';
            fileName.textContent = file.name || 'foto-buku';
            if (actions) actions.style.display = 'flex';
        }

        function renderBorrowerImageSearchResults(books, message) {
            const container = getBorrowerImageSearchResults();

            if (!container) {
                return;
            }

            if (!Array.isArray(books) || books.length === 0) {
                container.style.display = 'block';
                container.innerHTML = '<div class="text-sm text-slate2-400">Buku tidak ditemukan.</div>';
                return;
            }

            container.style.display = 'block';
            container.innerHTML = '<div class="dbx-book-section-sub" style="margin-bottom:12px;">' + escapeHtml(message || 'Buku hasil pencarian foto') + '</div>'
                + '<div class="dbx-book-grid">' + books.map(function (book) {
                    const chipClass = book.borrow_state === 'available' ? '' : ' unavailable';
                    const chipLabel = getBorrowStateLabel(book.borrow_state);
                    const imageHtml = book.cover_url
                        ? '<img src="' + escapeHtml(book.cover_url) + '" alt="' + escapeHtml(book.title) + '">'
                        : '<div class="dbx-book-fallback">' + escapeHtml((book.title || 'B').trim().charAt(0).toUpperCase()) + '</div>';

                    return '<article class="dbx-book-card js-borrow-book"'
                        + ' role="button" tabindex="0"'
                        + ' data-id="' + escapeHtml(book.id) + '"'
                        + ' data-title="' + escapeHtml(book.title) + '"'
                        + ' data-author="' + escapeHtml(book.author || 'Penulis tidak tersedia') + '"'
                        + ' data-category="' + escapeHtml(book.category_name || book.category || 'Tanpa kategori') + '"'
                        + ' data-stock="' + escapeHtml(book.stock) + '"'
                        + ' data-cover-url="' + escapeHtml(book.cover_url || '') + '"'
                        + ' data-borrowed-at="' + escapeHtml(book.borrowed_at || '') + '"'
                        + ' data-due-at="' + escapeHtml(book.due_at || '') + '"'
                        + ' data-borrow-state="' + escapeHtml(book.borrow_state || 'available') + '"'
                        + ' data-can-borrow="' + (book.can_borrow ? '1' : '0') + '">'
                        + '<div class="dbx-book-thumb">' + imageHtml + '</div>'
                        + '<div class="dbx-book-body">'
                        + '<div class="dbx-book-chip' + chipClass + '">' + chipLabel + '</div>'
                        + '<div class="dbx-book-name">' + escapeHtml(book.title) + '</div>'
                        + '<div class="dbx-book-author">' + escapeHtml(book.author || 'Penulis tidak tersedia') + '</div>'
                        + '<div class="dbx-book-meta"><span class="dbx-book-stock">' + escapeHtml(book.stock) + ' stok</span><span>' + escapeHtml(book.category_name || book.category || 'Umum') + '</span></div>'
                        + '<div class="dbx-book-open"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>Lihat</div>'
                        + '</div></article>';
                }).join('') + '</div>';

            if (window.lucide) {
                window.lucide.createIcons();
            }

            bindBorrowBookCards();
        }

        async function searchBorrowerBooksByImage(file) {
            const status = getBorrowerImageSearchStatus();
            const results = getBorrowerImageSearchResults();

            if (!file || !status || !results) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');
            formData.append('image', file);

            status.textContent = 'Mencari dari foto...';
            results.style.display = 'block';
            results.innerHTML = '<div class="text-sm text-slate2-400">Sedang mencari buku...</div>';

            try {
                const response = await fetch('{{ route('admin.books.search-by-image') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Pencarian foto gagal.');
                }

                const books = Array.isArray(data.books) ? data.books : [];
                if (books.length === 0) {
                    status.textContent = '';
                    results.innerHTML = '<div class="text-sm text-slate2-400">Buku tidak ditemukan.</div>';
                    return;
                }

                status.textContent = data.message || 'Pencarian selesai.';
                renderBorrowerImageSearchResults(books, data.message || 'Hasil pencarian foto');
            } catch (error) {
                status.textContent = error.message || 'Pencarian foto gagal.';
                results.innerHTML = '<div class="text-sm text-slate2-400">Gagal memproses foto. Coba foto yang lebih jelas.</div>';
            }
        }

        function syncBorrowerBookCategories(categories, selectedValue) {
            const borrowerBookCategoryFilter = getBorrowerBookCategoryFilter();

            if (!borrowerBookCategoryFilter || !Array.isArray(categories)) {
                return;
            }

            borrowerBookCategoryFilter.innerHTML = '<option value="">Semua kategori</option>' + categories.map(function (category) {
                const selected = selectedValue === category.slug ? ' selected' : '';
                return '<option value="' + escapeHtml(category.slug) + '"' + selected + '>' + escapeHtml(category.name) + '</option>';
            }).join('');
        }

        async function refreshBorrowerBooks() {
            const borrowerBookGrid = getBorrowerBookGrid();
            const borrowerBookKeyword = getBorrowerBookKeyword();
            const borrowerBookCategoryFilter = getBorrowerBookCategoryFilter();
            const borrowerBookAvailabilityFilter = getBorrowerBookAvailabilityFilter();

            if (!borrowerBookGrid || !borrowerBookKeyword || !borrowerBookCategoryFilter || !borrowerBookAvailabilityFilter) {
                return;
            }

            const activeDrawerBookId = borrowDrawer?.classList.contains('open')
                ? (document.getElementById('borrowDrawerBookId')?.value || '')
                : '';
            const params = new URLSearchParams({
                q: (borrowerBookKeyword.value || '').trim(),
                category: borrowerBookCategoryFilter.value || '',
                availability: borrowerBookAvailabilityFilter.value || 'available'
            });

            try {
                const response = await fetch('{{ route('borrower.books') }}?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                syncBorrowerBookCategories(Array.isArray(data.categories) ? data.categories : [], params.get('category') || '');
                renderBorrowerBooks(Array.isArray(data.books) ? data.books : []);

                if (activeDrawerBookId && borrowDrawer?.classList.contains('open')) {
                    const nextBookCard = borrowerBookGrid.querySelector('[data-id="' + CSS.escape(activeDrawerBookId) + '"]');
                    if (nextBookCard) {
                        openBorrowDrawer(nextBookCard);
                    }
                }
            } catch (error) {
                console.error('Error fetching borrower books:', error);
            }
        }

        let borrowerSearchTimeout;

        document.addEventListener('input', function (event) {
            if (event.target?.id !== 'borrowerBookKeyword') {
                return;
            }

            clearTimeout(borrowerSearchTimeout);
            borrowerSearchTimeout = setTimeout(refreshBorrowerBooks, 300);
        });

        document.addEventListener('change', function (event) {
            if (event.target?.id === 'borrowerBookCategoryFilter' || event.target?.id === 'borrowerBookAvailabilityFilter') {
                refreshBorrowerBooks();
            }
        });

        document.addEventListener('submit', function (event) {
            const borrowerBookFilterForm = event.target.closest('#borrowerBookFilterForm');

            if (!borrowerBookFilterForm) {
                return;
            }

            event.preventDefault();

            const file = borrowerImageSearchCameraInput && borrowerImageSearchCameraInput.files && borrowerImageSearchCameraInput.files[0]
                ? borrowerImageSearchCameraInput.files[0]
                : (borrowerImageSearchGalleryInput && borrowerImageSearchGalleryInput.files && borrowerImageSearchGalleryInput.files[0]
                    ? borrowerImageSearchGalleryInput.files[0]
                    : null);

            if (file) {
                searchBorrowerBooksByImage(file);
                return;
            }

            refreshBorrowerBooks();
        });

        const borrowerImageSearchCameraInput = getBorrowerImageSearchCameraInput();
        const borrowerImageSearchGalleryInput = getBorrowerImageSearchGalleryInput();
        const borrowerImageSearchSourceBtn = document.getElementById('borrowerImageSearchSourceBtn');
        const borrowerImageSearchSourceMenu = document.getElementById('borrowerImageSearchSourceMenu');
        const borrowerImageSearchCamera = document.getElementById('borrowerImageSearchCamera');
        const borrowerImageSearchGallery = document.getElementById('borrowerImageSearchGallery');
        if (borrowerImageSearchCameraInput) {
            borrowerImageSearchCameraInput.addEventListener('change', function () {
                const file = borrowerImageSearchCameraInput.files && borrowerImageSearchCameraInput.files[0] ? borrowerImageSearchCameraInput.files[0] : null;
                if (borrowerImageSearchGalleryInput) {
                    borrowerImageSearchGalleryInput.value = '';
                }
                setBorrowerImageSearchPreview(file);
            });
        }

        if (borrowerImageSearchGalleryInput) {
            borrowerImageSearchGalleryInput.addEventListener('change', function () {
                const file = borrowerImageSearchGalleryInput.files && borrowerImageSearchGalleryInput.files[0] ? borrowerImageSearchGalleryInput.files[0] : null;
                if (borrowerImageSearchCameraInput) {
                    borrowerImageSearchCameraInput.value = '';
                }
                setBorrowerImageSearchPreview(file);
            });
        }

        if (borrowerImageSearchCamera) {
            borrowerImageSearchCamera.addEventListener('click', function () {
                if (borrowerImageSearchCameraInput) {
                    borrowerImageSearchCameraInput.click();
                }
                if (borrowerImageSearchSourceMenu) {
                    borrowerImageSearchSourceMenu.style.display = 'none';
                }
            });
        }

        if (borrowerImageSearchGallery) {
            borrowerImageSearchGallery.addEventListener('click', function () {
                if (borrowerImageSearchGalleryInput) {
                    borrowerImageSearchGalleryInput.click();
                }
                if (borrowerImageSearchSourceMenu) {
                    borrowerImageSearchSourceMenu.style.display = 'none';
                }
            });
        }

        if (borrowerImageSearchSourceBtn && borrowerImageSearchSourceMenu) {
            borrowerImageSearchSourceBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                borrowerImageSearchSourceMenu.style.display = borrowerImageSearchSourceMenu.style.display === 'block' ? 'none' : 'block';
            });
        }

        document.addEventListener('click', function (event) {
            if (!borrowerImageSearchSourceMenu) {
                return;
            }

            const clickedInside = event.target.closest('#borrowerImageSearchSourceMenu') || event.target.closest('#borrowerImageSearchSourceBtn');
            if (!clickedInside) {
                borrowerImageSearchSourceMenu.style.display = 'none';
            }
        });

        document.addEventListener('notificationsUpdated', function (event) {
            const data = event.detail;
            const notifications = Array.isArray(data.notifications) ? data.notifications : [];
            const dashboardWrap = document.getElementById('asyncDashboardWrap');
            const borrowerNotifList = getBorrowerNotifList();
            const sanctionAlert = getSanctionAlert();
            const accountStatus = getAccountStatus();

            if (dashboardWrap?.dataset.dashboardRole === 'principal') {
                const nextSignatures = notifications
                    .map(function (notification) {
                        return notification.signature || '';
                    })
                    .filter(Boolean)
                    .join('|');

                if (dashboardWrap.dataset.principalSignatures !== nextSignatures) {
                    dashboardWrap.dataset.principalSignatures = nextSignatures;
                    refreshAsyncTargets(['#asyncDashboardWrap']).catch(function (error) {
                        console.error('Error refreshing principal dashboard:', error);
                    });
                }

                return;
            }

            if (dashboardWrap?.dataset.dashboardRole === 'borrower') {
                const nextSignatures = notifications
                    .map(function (notification) {
                        return notification.signature || '';
                    })
                    .filter(Boolean)
                    .join('|');
                const nextStateSignature = data.signature || nextSignatures;
                const stateChanged = dashboardWrap.dataset.borrowerStateSignature !== nextStateSignature;
                const notificationChanged = dashboardWrap.dataset.borrowerSignatures !== nextSignatures;

                if (stateChanged || notificationChanged) {
                    dashboardWrap.dataset.borrowerSignatures = nextSignatures;
                    dashboardWrap.dataset.borrowerStateSignature = nextStateSignature;
                    refreshBorrowerBooks().catch(function (error) {
                        console.error('Error refreshing borrower books:', error);
                    });
                }
            }

            // Update dashboard notif list
            if (borrowerNotifList) {
                if (notifications.length === 0) {
                    borrowerNotifList.innerHTML = '';
                    borrowerNotifList.style.display = 'none';
                } else {
                    borrowerNotifList.style.display = 'grid';
                    borrowerNotifList.innerHTML = notifications.map(function (n) {
                        return '<article class="dbx-notif-item ' + (n.tone || 'info') + '">'
                            + '<div class="dbx-notif-title">' + (n.title || 'Notifikasi') + '</div>'
                            + '<div class="dbx-notif-body">' + (n.body || '') + '</div>'
                            + '</article>';
                    }).join('');
                }
            }

            // Update sanction alert
            if (sanctionAlert) {
                if (data.sanction_message) {
                    sanctionAlert.style.display = 'block';
                    sanctionAlert.textContent = data.sanction_message;
                } else {
                    sanctionAlert.style.display = 'none';
                }
            }

            // Update account status
            if (accountStatus && data.account_status) {
                accountStatus.innerHTML = 'Status akun: <strong>' + escapeHtml(data.account_status) + '</strong>';
            }

            // Update loan stats
            if (data.borrower_loan_stats) {
                const reqEl = document.getElementById('stat-requested');
                const borEl = document.getElementById('stat-borrowed');
                const retEl = document.getElementById('stat-returned');
                
                if (reqEl) reqEl.textContent = data.borrower_loan_stats.requested || 0;
                if (borEl) borEl.textContent = data.borrower_loan_stats.borrowed || 0;
                if (retEl) retEl.textContent = data.borrower_loan_stats.returned || 0;
            }
        });

        // Make closeBorrowDrawer globally accessible for data-success-call
        window.closeBorrowDrawer = function() {
            const borrowDrawer = document.getElementById('borrowDrawer');
            const borrowDrawerMask = document.getElementById('borrowDrawerMask');
            if (!borrowDrawer || !borrowDrawerMask) {
                return;
            }

            borrowDrawer.classList.remove('open');
            borrowDrawerMask.classList.remove('show');
            borrowDrawer.setAttribute('aria-hidden', 'true');
            setBorrowerRealtimePause(false);
        };

        function syncBorrowDrawerDueAt() {
            const borrowedAt = document.getElementById('borrowDrawerBorrowedAt');
            const dueAt = document.getElementById('borrowDrawerDueAt');

            if (!borrowedAt || !dueAt || !borrowedAt.value) {
                return;
            }

            const borrowedDate = new Date(borrowedAt.value + 'T00:00:00');
            borrowedDate.setDate(borrowedDate.getDate() + 1);
            dueAt.value = borrowedDate.toISOString().slice(0, 10);
        }

        function openBorrowDrawer(button) {
            if (!borrowDrawer || !borrowDrawerMask || !button) {
                return;
            }

            const content = document.getElementById('borrowDrawerContent');
            const empty = document.getElementById('borrowDrawerEmpty');
            const image = document.getElementById('borrowDrawerImage');
            const fallback = document.getElementById('borrowDrawerFallback');
            const status = document.getElementById('borrowDrawerStatus');
            const title = document.getElementById('borrowDrawerTitle');
            const author = document.getElementById('borrowDrawerAuthor');
            const stock = document.getElementById('borrowDrawerStock');
            const category = document.getElementById('borrowDrawerCategory');
            const bookId = document.getElementById('borrowDrawerBookId');
            const borrowedAt = document.getElementById('borrowDrawerBorrowedAt');
            const submit = document.getElementById('borrowDrawerSubmit');
            const submitLabel = document.getElementById('borrowDrawerSubmitLabel');
            const borrowState = button.dataset.borrowState || 'unavailable';
            const isAvailable = borrowState === 'available';
            const bookTitle = button.dataset.title || 'Judul buku';

            empty.style.display = 'none';
            content.style.display = 'block';

            title.textContent = bookTitle;
            author.textContent = button.dataset.author || 'Penulis tidak tersedia';
            stock.textContent = 'Stok ' + (button.dataset.stock || '0');
            category.textContent = button.dataset.category || 'Tanpa kategori';
            bookId.value = button.dataset.id || '';
            borrowedAt.value = button.dataset.borrowedAt || '';
            syncBorrowDrawerDueAt();
            fallback.textContent = (bookTitle.trim().charAt(0) || 'B').toUpperCase();

            status.textContent = getBorrowStateLabel(borrowState) === 'Habis'
                ? 'Tidak tersedia'
                : getBorrowStateLabel(borrowState);
            status.classList.toggle('unavailable', !isAvailable);
            submit.disabled = !isAvailable;
            submitLabel.textContent = getBorrowDrawerLabel(borrowState, isAvailable);

            if (button.dataset.coverUrl) {
                image.src = button.dataset.coverUrl;
                image.alt = bookTitle;
                image.style.display = 'block';
                fallback.style.display = 'none';
            } else {
                image.src = '';
                image.alt = '';
                image.style.display = 'none';
                fallback.style.display = 'flex';
            }

            borrowDrawer.classList.add('open');
            borrowDrawerMask.classList.add('show');
            borrowDrawer.setAttribute('aria-hidden', 'false');
            setBorrowerRealtimePause(true);
        }

        bindBorrowBookCards();

        if (borrowDrawerMask) {
            borrowDrawerMask.addEventListener('click', closeBorrowDrawer);
        }

        const borrowDrawerClose = document.getElementById('borrowDrawerClose');
        if (borrowDrawerClose) {
            borrowDrawerClose.addEventListener('click', closeBorrowDrawer);
        }

        const borrowDrawerBorrowedAt = document.getElementById('borrowDrawerBorrowedAt');
        if (borrowDrawerBorrowedAt) {
            borrowDrawerBorrowedAt.addEventListener('change', syncBorrowDrawerDueAt);
            syncBorrowDrawerDueAt();
        }

        let borrowerLoanSubmitBusy = false;

        const borrowerLoanForm = document.getElementById('borrowerLoanForm');
        if (borrowerLoanForm) {
            borrowerLoanForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const submitBtn = document.getElementById('borrowDrawerSubmit');
                const submitLabel = document.getElementById('borrowDrawerSubmitLabel');
                const bookId = document.getElementById('borrowDrawerBookId')?.value || '';
                const originalLabel = submitLabel.textContent;
                
                // Disable button and show loading
                borrowerLoanSubmitBusy = true;
                setBorrowerRealtimePause(true);
                submitBtn.disabled = true;
                submitLabel.textContent = 'Sedang mengirim...';

                const formData = new FormData(borrowerLoanForm);

                fetch(borrowerLoanForm.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(async function (response) {
                    const rawBody = await response.text();
                    let result = {};

                    try {
                        result = rawBody ? JSON.parse(rawBody) : {};
                    } catch (parseError) {
                        result = { message: rawBody || 'Terjadi kesalahan saat mengirim pengajuan.' };
                    }

                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || ('Terjadi kesalahan saat mengirim pengajuan. Status: ' + response.status));
                    }

                    return result;
                })
                    .then(function (result) {
                        applyBorrowerLoanRequestOptimistic(bookId);
                        borrowerLoanForm.reset();
                        showToast({
                            title: 'Berhasil!',
                            body: result.message || 'Pengajuan berhasil dikirim.',
                            tone: 'success'
                        });

                        closeBorrowDrawer();
                        submitLabel.textContent = originalLabel;
                        borrowerLoanSubmitBusy = false;
                        submitBtn.disabled = false;
                        setBorrowerRealtimePause(false);

                        Promise.allSettled([
                            refreshGlobalNotifications(false),
                            refreshBorrowerBooks(),
                        ]);
                    })
                    .catch(function (error) {
                        showToast({
                            title: 'Gagal',
                            body: error.message || 'Terjadi kesalahan saat mengirim pengajuan.',
                            tone: 'danger'
                        });

                        borrowerLoanSubmitBusy = false;
                        submitBtn.disabled = false;
                        setBorrowerRealtimePause(false);
                        submitLabel.textContent = originalLabel;
                        refreshBorrowerBooks().catch(function (refreshError) {
                            console.error('Error restoring borrower books:', refreshError);
                        });
                    });
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeBorrowDrawer();
            }
        });

        let borrowerRealtimeBusy = false;

        async function syncBorrowerRealtime() {
            const dashboardWrap = document.getElementById('asyncDashboardWrap');
            const borrowerBookGrid = getBorrowerBookGrid();

            if (!borrowerBookGrid || borrowerRealtimeBusy || borrowerLoanSubmitBusy || document.hidden || dashboardWrap?.dataset.dashboardRole !== 'borrower') {
                return;
            }

            borrowerRealtimeBusy = true;

            try {
                await Promise.all([
                    refreshGlobalNotifications(false),
                    refreshBorrowerBooks(),
                ]);
            } finally {
                borrowerRealtimeBusy = false;
            }
        }

        if (getBorrowerBookGrid()) {
            syncBorrowerRealtime();
            window.setInterval(function () {
                syncBorrowerRealtime();
            }, 10000);

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    syncBorrowerRealtime();
                }
            });
        }

        // Auto refresh entire dashboard for stats/logs
        window.setInterval(function() {
            const dashboardWrap = document.querySelector('#asyncDashboardWrap');
            if (dashboardWrap && dashboardWrap.dataset.dashboardRole !== 'borrower') {
                refreshAsyncTargets(['#asyncDashboardWrap']);
            }
        }, 30000);

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
