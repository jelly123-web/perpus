<?php $__env->startSection('content'); ?>
<?php
    $title = 'Riwayat Peminjaman';
    $eyebrow = 'Akun Peminjam';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    :root {
        --dbx-primary: #f97316;
        --dbx-primary-hover: #ea580c;
        --dbx-primary-light: #fff7ed;
        --dbx-bg: #f8fafc;
        --dbx-card-bg: #ffffff;
        --dbx-text: #1e293b;
        --dbx-text-muted: #64748b;
        --dbx-border: #e2e8f0;
        --dbx-success: #22c55e;
        --dbx-danger: #ef4444;
        --dbx-warning: #eab308;
    }

    .report-page {
        max-width: 1480px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding-bottom: 32px;
        width: min(100%, calc(100vw - 72px));
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    }

    .report-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
        padding-bottom: 0;
        border-bottom: none;
    }

    .report-title-wrap, .report-title-copy { gap: 4px; }
    .report-title {
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 0;
        color: var(--dbx-text);
        line-height: 1.2;
        margin: 0;
    }

    .report-subtitle {
        font-size: 14px;
        color: var(--dbx-text-muted);
        font-weight: 400;
        margin-top: 4px;
    }

    .report-stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .report-stat-card {
        background: #fff;
        border: 1px solid var(--dbx-border);
        border-radius: 12px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        gap: 12px;
        box-shadow: none;
    }

    .report-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--dbx-border);
    }

    .report-stat-card.books-card::before { background: var(--dbx-primary); }
    .report-stat-card.loans-card::before { background: var(--dbx-warning); }
    .report-stat-card.returns-card::before { background: var(--dbx-success); }
    .report-stat-card.status-card::before { background: var(--dbx-text-muted); }

    .report-stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .report-stat-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .report-stat-icon-box.books { background: #fff7ed; color: var(--dbx-primary); }
    .report-stat-icon-box.loans { background: #fefce8; color: var(--dbx-warning); }
    .report-stat-icon-box.returns { background: #f0fdf4; color: var(--dbx-success); }
    .report-stat-icon-box.status { background: #f1f5f9; color: var(--dbx-text-muted); }

    .report-stat-value {
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--dbx-text);
        letter-spacing: 0;
    }

    .report-stat-value.is-name {
        font-size: 20px;
        line-height: 1.3;
    }

    .report-stat-label {
        font-size: 13px;
        font-weight: 400;
        margin-top: 4px;
        color: var(--dbx-text-muted);
    }

    .report-stat-footer {
        font-size: 12px;
        margin-top: 12px;
        color: var(--dbx-text-muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
    }

    .footer-active { background: #dcfce7; color: #166534; }
    .footer-sanctioned { background: #fee2e2; color: #991b1b; }

    .history-alert {
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #fee2e2;
        background: #fff7ed;
        color: #9a3412;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .report-section-card {
        background: #fff;
        border: 1px solid var(--dbx-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .report-section-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--dbx-border);
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .report-section-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .report-section-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #fff7ed;
        color: var(--dbx-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .report-section-title-text {
        font-family: Inter, sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--dbx-text);
        margin: 0;
    }

    .report-section-subtitle-text {
        font-size: 13px;
        color: var(--dbx-text-muted);
        margin: 0;
    }

    .report-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    .report-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--dbx-text-muted);
        background: var(--dbx-bg);
        border-bottom: 1px solid var(--dbx-border);
        letter-spacing: 0;
    }

    .report-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--dbx-border);
        vertical-align: middle;
        color: var(--dbx-text);
        font-size: 14px;
    }

    .report-table tbody tr:nth-child(even) { background: transparent; }
    .report-table tr:hover td { background: #f8fafc; }

    .report-book-info-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .report-book-cover {
        width: 36px;
        height: 50px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #94a3b8;
        border: 1px solid var(--dbx-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .report-book-title {
        font-weight: 700;
        color: var(--dbx-text);
        display: block;
    }

    .report-book-author {
        font-size: 12px;
        color: var(--dbx-text-muted);
        margin-top: 2px;
        display: block;
    }

    .report-pill-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .report-pill-badge.borrowed,
    .report-pill-badge.requested {
        background: #fff7ed;
        color: var(--dbx-primary);
    }

    .report-pill-badge.late {
        background: #fee2e2;
        color: #991b1b;
    }

    .report-pill-badge.returned {
        background: #dcfce7;
        color: #166534;
    }

    .report-meta-dates {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .report-date-main {
        font-weight: 600;
        font-size: 13px;
    }

    .report-date-sub {
        font-size: 11px;
        color: var(--dbx-text-muted);
    }

    .text-red { color: var(--dbx-danger); font-weight: 600; }
    .text-green { color: var(--dbx-success); }
    .report-note { font-size: 13px; color: var(--dbx-text-muted); line-height: 1.55; }
    .report-note-sub { font-size: 12px; margin-top: 4px; font-style: italic; }
    .report-empty { padding: 38px 20px; text-align: center; color: var(--dbx-text-muted); }
    .report-pagination { padding: 16px 20px 20px; }

    @media (max-width: 768px) {
        .report-toolbar { flex-direction: column; align-items: stretch; }
        .report-stats-container { grid-template-columns: 1fr; }
        .report-page { width: min(100%, calc(100vw - 24px)); }
    }
</style>

<?php
    $statusLabels = [
        'requested' => 'Menunggu',
        'borrowed' => 'Dipinjam',
        'late' => 'Terlambat',
        'returned' => 'Dikembalikan',
    ];
?>

<div class="report-page">
    <div class="report-toolbar">
        <div class="report-title-wrap">
            <div class="report-title-copy">
                <h1 class="report-title">Riwayat Peminjaman</h1>
                <div class="report-subtitle">Cek buku yang sedang dipinjam, riwayat transaksi, dan status akun Anda.</div>
            </div>
        </div>
    </div>

    <section class="report-stats-container">
        <div class="report-stat-card books-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box books"><i data-lucide="book-open-check" class="w-5 h-5"></i></div>
            </div>
            <div class="report-stat-value"><?php echo e($borrowerHistoryStats['active_loans']); ?></div>
            <div class="report-stat-label">Buku sedang dipinjam</div>
        </div>
        <div class="report-stat-card loans-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box loans"><i data-lucide="hourglass" class="w-5 h-5"></i></div>
            </div>
            <div class="report-stat-value"><?php echo e($borrowerHistoryStats['requested']); ?></div>
            <div class="report-stat-label">Pengajuan menunggu</div>
        </div>
        <div class="report-stat-card returns-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box returns"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
            </div>
            <div class="report-stat-value"><?php echo e($borrowerHistoryStats['returned']); ?></div>
            <div class="report-stat-label">Riwayat selesai</div>
        </div>
        <div class="report-stat-card status-card">
            <div class="report-stat-header">
                <div class="report-stat-icon-box status"><i data-lucide="user-check" class="w-5 h-5"></i></div>
            </div>
            <div class="report-stat-value is-name"><?php echo e(auth()->user()?->name); ?></div>
            <div class="report-stat-label">Status Akun</div>
            <div class="report-stat-footer <?php echo e($borrowerActiveSanction ? 'footer-sanctioned' : 'footer-active'); ?>">
                <?php echo e($borrowerHistoryStats['account_status']); ?>

            </div>
        </div>
    </section>

    <?php if($borrowerActiveSanction): ?>
        <div class="history-alert">
            <i data-lucide="triangle-alert" class="w-5 h-5"></i>
            <div>
                Akun Anda sedang kena sanksi.
                <?php if($borrowerActiveSanction->ends_at): ?>
                    Masa sanksi sampai <?php echo e($borrowerActiveSanction->ends_at->translatedFormat('d M Y')); ?>.
                <?php endif; ?>
                Selama sanksi aktif, Anda belum bisa meminjam buku lagi.
            </div>
        </div>
    <?php endif; ?>

    <section class="report-section-card">
        <div class="report-section-header">
            <div class="report-section-info">
                <div class="report-section-icon"><i data-lucide="history" class="w-5 h-5"></i></div>
                <div>
                    <h2 class="report-section-title-text">Daftar Riwayat</h2>
                    <p class="report-section-subtitle-text">Semua pengajuan, peminjaman, dan pengembalian.</p>
                </div>
            </div>
        </div>

        <?php if($borrowerLoans->count()): ?>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Informasi Buku</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $borrowerLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $title = $loan->book?->title ?? 'Buku tidak ditemukan';
                                $initials = collect(explode(' ', $title))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($word) => strtoupper(mb_substr($word, 0, 1)))
                                    ->implode('');
                            ?>
                            <tr>
                                <td>
                                    <div class="report-book-info-cell">
                                        <div class="report-book-cover"><?php echo e($initials ?: 'BK'); ?></div>
                                        <div>
                                            <span class="report-book-title"><?php echo e($title); ?></span>
                                            <span class="report-book-author"><?php echo e($loan->book?->author ?? 'Penulis tidak tersedia'); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="report-pill-badge <?php echo e($loan->status); ?>">
                                        <?php echo e($statusLabels[$loan->status] ?? ucfirst($loan->status)); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="report-meta-dates">
                                        <?php if($loan->status === 'returned'): ?>
                                            <span class="report-date-sub">Pinjam:</span>
                                            <span class="report-date-main"><?php echo e(optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                            <span class="report-date-sub" style="margin-top:4px;">Kembali:</span>
                                            <span class="report-date-main text-green"><?php echo e(optional($loan->returned_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                        <?php else: ?>
                                            <span class="report-date-sub">Pinjam:</span>
                                            <span class="report-date-main"><?php echo e(optional($loan->borrowed_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                            <span class="report-date-sub" style="margin-top:4px;">Tenggat:</span>
                                            <span class="report-date-main <?php echo e($loan->status === 'late' ? 'text-red' : ''); ?>"><?php echo e(optional($loan->due_at)->translatedFormat('d M Y') ?? '-'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="report-note">
                                        <div>Petugas: <?php echo e($loan->processor?->name ?? ($loan->status === 'requested' ? 'Menunggu petugas' : '-')); ?></div>
                                        <div class="report-note-sub <?php echo e($loan->status === 'late' ? 'text-red' : ($loan->status === 'returned' ? 'text-green' : '')); ?>">
                                            <?php echo e($loan->notes
                                                    ?: match ($loan->status) {
                                                        'late' => 'Harap segera dikembalikan',
                                                        'returned' => 'Tepat waktu',
                                                        default => '-',
                                                    }); ?>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="report-pagination"><?php echo e($borrowerLoans->links()); ?></div>
        <?php else: ?>
            <div class="report-empty">Belum ada riwayat peminjaman di akun ini.</div>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\member\history.blade.php ENDPATH**/ ?>