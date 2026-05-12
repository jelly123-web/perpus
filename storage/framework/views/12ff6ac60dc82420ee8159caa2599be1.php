<?php $__env->startSection('content'); ?>
<?php ($title = 'Peminjaman Buku'); ?>
<?php ($eyebrow = 'Petugas Perpustakaan'); ?>

<style>
    .loan-shell{display:grid;grid-template-columns:minmax(300px,1fr) minmax(0,1.5fr);gap:24px;width:100%}
    .loan-stack{display:flex;flex-direction:column;gap:24px}
    .loan-card{background:var(--dbx-card-bg, #fff);border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;box-shadow:none;overflow:hidden;transition:.2s}
    .loan-card:hover{box-shadow:none}
    .loan-card-header{padding:20px 24px;border-bottom:1px solid var(--dbx-border, #e2e8f0);display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fafafa}
    .loan-card-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:700;color:var(--dbx-text, #1e293b);margin:0}
    .loan-card-subtitle{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:4px;line-height:1.5}
    .loan-card-body{padding:24px}

    .loan-form-grid{display:flex;flex-direction:column;gap:20px}
    .loan-field{display:flex;flex-direction:column;gap:8px}
    .loan-label{font-size:12px;font-weight:600;color:var(--dbx-text-muted, #64748b);text-transform:uppercase;letter-spacing:0}
    .loan-input-group{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .loan-form-grid input[type="date"]::-webkit-calendar-picker-indicator{display:none;-webkit-appearance:none}
    .loan-form-grid input[type="date"]{appearance:none;-webkit-appearance:none}

    .loan-form-grid .form-input,.loan-form-grid .form-select{
        width:100%;
        padding:10px 14px!important;
        border:1px solid var(--dbx-border, #e2e8f0);
        border-radius:8px!important;
        background:#fff;
        font-size:14px;
        color:var(--dbx-text, #1e293b);
        outline:none;
        box-shadow:none;
    }
    .loan-form-grid .form-input:focus,.loan-form-grid .form-select:focus{
        border-color:var(--dbx-primary, #f97316);
        box-shadow:0 0 0 3px rgba(249,115,22,.1);
    }

    .loan-item-card{padding:20px;border-radius:12px;background:#fff;border:1px solid var(--dbx-border, #e2e8f0);display:flex;flex-direction:column;gap:14px;transition:.2s ease}
    .loan-item-card:hover{background:#fff;transform:none;box-shadow:none}
    .loan-item-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .loan-item-name{font-size:16px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .loan-item-info{font-size:14px;color:var(--dbx-text-muted, #64748b);line-height:1.6}
    .loan-item-badge{padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;box-shadow:none}
    .loan-item-badge.pending{background:#fff7ed;color:var(--dbx-primary, #f97316)}
    .loan-item-badge.active{background:#fee2e2;color:#991b1b}
    .loan-item-badge.done{background:#dcfce7;color:#166534}

    .loan-table-wrap{width:100%;overflow-x:auto}
    .loan-table{width:100%;border-collapse:collapse;table-layout:fixed}
    .loan-table th{text-align:left;padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--dbx-text-muted, #64748b);background:var(--dbx-bg, #f8fafc);border-bottom:1px solid var(--dbx-border, #e2e8f0);letter-spacing:0}
    .loan-table td{padding:16px;border-bottom:1px solid var(--dbx-border, #e2e8f0);font-size:14px;color:var(--dbx-text, #1e293b);vertical-align:middle;word-wrap:break-word}
    .loan-table tr:hover td{background:#f8fafc}

    .loan-book-box{display:flex;align-items:center;gap:12px}
    .loan-book-cover-mini{width:40px;height:55px;border-radius:4px;overflow:hidden;background:#f1f5f9;color:#cbd5e1;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;box-shadow:none}
    .loan-book-cover-mini img{width:100%;height:100%;object-fit:cover}
    .loan-book-title-text{font-weight:600;color:var(--dbx-text, #1e293b);font-size:14px;line-clamp:1;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden}
    .loan-book-author-text{font-size:12px;color:var(--dbx-text-muted, #64748b);margin-top:2px}

    .loan-status-select{width:100%;padding:8px 12px;border-radius:6px;border:1px solid var(--dbx-border, #e2e8f0);background:#fff;font-size:13px;font-weight:600;color:var(--dbx-text, #1e293b);cursor:pointer;transition:.2s ease;appearance:none;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 10px center;padding-right:34px}
    .loan-status-select:hover,.loan-status-select:focus{border-color:var(--dbx-primary, #f97316);box-shadow:0 0 0 3px rgba(249,115,22,.1)}

    .btn-loan-submit{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:8px;background:var(--dbx-primary, #f97316);border:none;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:.2s;width:100%;height:auto;box-shadow:none}
    .btn-loan-submit:hover{background:var(--dbx-primary-hover, #ea580c);color:#fff;transform:none;box-shadow:none}
    .btn-loan-submit:active{transform:none;box-shadow:none}

    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px}
    .report-usage-widget{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:20px;box-shadow:none;display:flex;flex-direction:column;gap:8px;min-height:unset;transition:.2s ease}
    .report-usage-widget:hover{background:#fff}
    .report-usage-tag{font-size:11px;font-weight:700;color:var(--dbx-text-muted, #64748b);text-transform:uppercase;letter-spacing:.05em}
    .report-usage-number{font-size:24px;font-weight:800;color:var(--dbx-text, #1e293b)}
    .report-usage-desc{font-size:12px;color:var(--dbx-text-muted, #64748b);font-weight:400}

    @media (max-width:1100px){.loan-shell{grid-template-columns:1fr}}
    @media (max-width:768px){.loan-input-group{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="member-toolbar" style="border-bottom: 1px solid var(--border-light); padding-bottom: 24px; margin-bottom: 32px;">
        <div>
            <h1 class="font-display member-title" style="font-size: 36px; font-weight: 800;">Peminjaman Buku</h1>
            <p class="member-subtitle" style="font-size: 16px; color: var(--muted); margin-top: 8px;">Input data peminjaman, catat nama peminjam, lalu tentukan tanggal pinjam dan tanggal kembali.</p>
        </div>
    </div>

    <section id="loanStatsWrap" class="report-usage-row" style="margin-bottom: 32px;">
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent);">Pengajuan Baru</div>
            <div class="report-usage-number" id="loanRequestedStat"><?php echo e($loanStats['requested']); ?></div>
            <div class="report-usage-desc">Menunggu proses</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--gold);">Total Pinjam</div>
            <div class="report-usage-number"><?php echo e($loanStats['total']); ?></div>
            <div class="report-usage-desc">Semua transaksi</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--teal);">Sedang Dipinjam</div>
            <div class="report-usage-number"><?php echo e($loanStats['borrowed']); ?></div>
            <div class="report-usage-desc">Buku di luar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent-dark);">Sudah Kembali</div>
            <div class="report-usage-number"><?php echo e($loanStats['returned']); ?></div>
            <div class="report-usage-desc">Selesai pinjam</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--red);">Terlambat</div>
            <div class="report-usage-number"><?php echo e($loanStats['late']); ?></div>
            <div class="report-usage-desc">Perlu tindakan</div>
        </div>
    </section>

    <div id="loanPageWrap" class="loan-shell">
        <div class="loan-stack">
            <!-- Section: Peminjaman Langsung -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Peminjaman Langsung</h2>
                        <p class="loan-card-subtitle">Petugas bisa input peminjaman secara manual tanpa menunggu pengajuan peminjam.</p>
                    </div>
                </div>
                <div class="loan-card-body">
                    <form method="POST" action="<?php echo e(route('admin.loans.store')); ?>" class="loan-form-grid" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        <?php echo csrf_field(); ?>
                        <div class="loan-field">
                            <label class="loan-label">Pilih Buku</label>
                            <select name="book_id" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                <option value="">Pilih buku</option>
                                <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($book->id); ?>">
                                        <?php echo e($book->title); ?> - <?php echo e($book->stock_available); ?> stok
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label">Nama Peminjam</label>
                            <input type="text" name="borrower_name" class="form-input px-4 py-3 text-sm rounded-xl" placeholder="Ketik nama peminjam..." required list="memberList">
                            <datalist id="memberList">
                                <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($member->name); ?>"><?php echo e($member->academicLabel()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </datalist>
                        </div>
                        <div class="loan-input-group">
                            <div class="loan-field">
                                <label class="loan-label">Tanggal Pinjam</label>
                                <input type="date" name="borrowed_at" id="directLoanBorrowedAt" class="form-input px-4 py-3 text-sm rounded-xl" value="<?php echo e(now()->toDateString()); ?>" required>
                            </div>
                            <div class="loan-field">
                                <label class="loan-label">Tanggal Kembali</label>
                                <input type="date" name="due_at" id="directLoanDueAt" class="form-input px-4 py-3 text-sm rounded-xl" value="<?php echo e(now()->addDay()->toDateString()); ?>" required>
                            </div>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label">Catatan</label>
                            <input type="text" name="notes" class="form-input px-4 py-3 text-sm rounded-xl" placeholder="Opsional, misal: input manual oleh petugas">
                        </div>
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="book-plus" class="w-4 h-4"></i> Simpan Peminjaman
                        </button>
                    </form>
                </div>
            </div>

            <div id="loanRequestedPanel">
                <?php echo $__env->make('admin.loans._requested-panel', ['requestedLoans' => $requestedLoans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <!-- Section: Kunci Akun Peminjam -->
            <div class="loan-card">
                <div class="loan-card-header">
                    <div>
                        <h2 class="loan-card-title">Status Akun Peminjam</h2>
                        <p class="loan-card-subtitle">Kunci akun peminjam secara manual sebagai sanksi.</p>
                    </div>
                </div>
                <div class="loan-card-body">
                    <form method="POST" action="<?php echo e(route('admin.loans.borrower-status.update')); ?>" class="loan-form-grid" data-async="true" data-reset-on-success="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        <?php echo csrf_field(); ?>
                        <div class="loan-field">
                            <label class="loan-label">Pilih Peminjam</label>
                            <select name="member_id" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                <option value="">Pilih peminjam</option>
                                <?php $__currentLoopData = $memberStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($member->id); ?>">
                                        <?php echo e($member->name); ?> - <?php echo e($member->borrower_status === 'sanctioned' ? '(Terkunci)' : 'Aktif'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="loan-input-group">
                            <div class="loan-field">
                                <label class="loan-label">Tindakan</label>
                                <select name="status" class="form-select px-4 py-3 text-sm rounded-xl" required>
                                    <option value="sanctioned">Kunci Akun (Beri Sanksi)</option>
                                    <option value="active">Buka Kunci (Aktifkan)</option>
                                </select>
                            </div>
                            <div class="loan-field">
                                <label class="loan-label">Durasi (Hari)</label>
                                <input type="number" name="duration_days" class="form-input px-4 py-3 text-sm rounded-xl" placeholder="Kosongkan jika permanen" min="1" max="365">
                            </div>
                        </div>
                        <div class="loan-field">
                            <label class="loan-label">Alasan Sanksi</label>
                            <input type="text" name="reason" class="form-input px-4 py-3 text-sm rounded-xl" placeholder="Contoh: Terlambat mengembalikan buku lebih dari 1 bulan">
                        </div>
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i> Terapkan Status
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
                        <i data-lucide="history" class="w-4 h-4"></i> <?php echo e($loans->total()); ?> Transaksi
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
                            <?php $__currentLoopData = $loans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="loan-book-box">
                                            <div class="loan-book-cover-mini">
                                                <?php if($loan->book?->cover_image): ?>
                                                    <img src="<?php echo e(asset('storage/' . $loan->book->cover_image)); ?>" alt="<?php echo e($loan->book?->title); ?>">
                                                <?php else: ?>
                                                    <i data-lucide="book-image" class="w-5 h-5"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="loan-book-title-text"><?php echo e($loan->book?->title ?? 'Buku tidak ditemukan'); ?></div>
                                                <div class="loan-book-author-text"><?php echo e($loan->book?->author ?? '-'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-900"><?php echo e($loan->member?->name ?? $loan->borrower_name); ?></span>
                                            <span class="text-xs text-slate2-500 mt-0.5"><?php echo e($loan->member?->academicLabel() ?? 'Peminjam Luar'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate2-800 text-sm"><?php echo e(optional($loan->borrowed_at)->translatedFormat('d M Y')); ?></span>
                                            <span class="text-[10px] text-slate2-400 uppercase tracking-wider font-black mt-1">Hingga <?php echo e(optional($loan->returned_at ?? $loan->due_at)->translatedFormat('d M Y')); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo e(route('admin.loans.update', $loan)); ?>" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <select name="status" class="loan-status-select" data-loan-status-select>
                                                <option value="requested" <?php if($loan->status === 'requested'): echo 'selected'; endif; ?>>Menunggu</option>
                                                <option value="borrowed" <?php if($loan->status === 'borrowed'): echo 'selected'; endif; ?>>Dipinjam</option>
                                                <option value="late" <?php if($loan->status === 'late'): echo 'selected'; endif; ?>>Terlambat</option>
                                                <option value="returned" <?php if($loan->status === 'returned'): echo 'selected'; endif; ?>>Selesai</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php if($loans->hasPages()): ?>
                    <div class="p-6 border-t border-slate2-100">
                        <?php echo e($loans->links()); ?>

                    </div>
                <?php endif; ?>
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
                        <?php $__empty_1 = true; $__currentLoopData = $sanctionMonitoring; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monitoring): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="loan-item-card">
                                <div class="loan-item-head">
                                    <div>
                                        <div class="loan-item-name"><?php echo e($monitoring->member?->name ?? 'Peminjam'); ?></div>
                                        <div class="loan-item-info mt-1 text-red-600 font-bold">
                                            <?php echo e($monitoring->reason); ?>

                                        </div>
                                    </div>
                                    <span class="loan-item-badge <?php echo e($monitoring->monitoring_state === 'active' ? 'active' : 'done'); ?>">
                                        <?php echo e($monitoring->monitoring_state === 'active' ? 'Disanksi' : 'Selesai'); ?>

                                    </span>
                                </div>
                                <div class="loan-item-info">
                                    <?php if($monitoring->ends_at): ?>
                                        Berakhir: <?php echo e(optional($monitoring->ends_at)->translatedFormat('d M Y')); ?>

                                    <?php else: ?>
                                        Durasi: <?php echo e($monitoring->duration_days); ?> hari
                                    <?php endif; ?>
                                </div>
                                <?php if($monitoring->monitoring_state !== 'completed'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.loans.sanctions.update', $monitoring)); ?>" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="completed">
                                        <button class="btn-report-action w-full mt-2" type="submit" style="font-size: 11px; height: 36px;">
                                            Tandai Aktif Kembali
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-full report-empty-state">
                                <div class="report-empty-icon"><i data-lucide="shield-check"></i></div>
                                <p class="report-empty-text">Tidak ada sanksi aktif saat ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let loanRequestedPanelSignature = null;
    let loanRequestedPanelBusy = false;

    function syncLoanDueAt(borrowedId, dueId) {
        const loanBorrowedAt = document.getElementById(borrowedId);
        const loanDueAt = document.getElementById(dueId);

        if (!loanBorrowedAt || !loanDueAt || !loanBorrowedAt.value) {
            return;
        }

        const borrowedDate = new Date(loanBorrowedAt.value + 'T00:00:00');
        borrowedDate.setDate(borrowedDate.getDate() + 1);
        loanDueAt.value = borrowedDate.toISOString().slice(0, 10);
    }

    function updateRequestedLoanCounters(requestedCount) {
        const requestedStat = document.getElementById('loanRequestedStat');
        const requestedBadge = document.getElementById('loanRequestedBadge');

        if (requestedStat) {
            requestedStat.textContent = requestedCount;
        }

        if (requestedBadge) {
            requestedBadge.textContent = requestedCount + ' Menunggu';
        }
    }

    async function syncRequestedLoanPanelSignature() {
        const requestedPanel = document.getElementById('loanRequestedPanel');

        if (!requestedPanel) {
            loanRequestedPanelSignature = null;
            return;
        }

        try {
            const response = await fetch('<?php echo e(route('admin.loans.requested-panel')); ?>?_t=' + Date.now(), {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            loanRequestedPanelSignature = data.signature || null;
            updateRequestedLoanCounters(Number(data.requested_count || 0));
        } catch (error) {
            console.error('Error syncing requested panel signature:', error);
        }
    }

    async function pollRequestedLoanPanel() {
        const requestedPanel = document.getElementById('loanRequestedPanel');

        if (!requestedPanel || loanRequestedPanelBusy || document.hidden) {
            return;
        }

        loanRequestedPanelBusy = true;

        try {
            const response = await fetch('<?php echo e(route('admin.loans.requested-panel')); ?>?_t=' + Date.now(), {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const nextSignature = data.signature || null;
            const requestedCount = Number(data.requested_count || 0);

            if (loanRequestedPanelSignature !== null && nextSignature && nextSignature !== loanRequestedPanelSignature && typeof data.html === 'string') {
                requestedPanel.innerHTML = data.html;

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            loanRequestedPanelSignature = nextSignature;
            updateRequestedLoanCounters(requestedCount);
        } catch (error) {
            console.error('Error polling requested panel:', error);
        } finally {
            loanRequestedPanelBusy = false;
        }
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'loanBorrowedAt') {
            syncLoanDueAt('loanBorrowedAt', 'loanDueAt');
        }

        if (event.target && event.target.id === 'directLoanBorrowedAt') {
            syncLoanDueAt('directLoanBorrowedAt', 'directLoanDueAt');
        }
    });

    function submitLoanStatusSelect(select) {
        const form = select?.form;

        if (!form || form.dataset.submitting === 'true') {
            return;
        }

        form.dataset.submitting = 'true';
        form.requestSubmit();

        window.setTimeout(function () {
            delete form.dataset.submitting;
        }, 250);
    }

    document.addEventListener('input', function (event) {
        const select = event.target.closest('[data-loan-status-select]');

        if (!select) {
            return;
        }

        submitLoanStatusSelect(select);
    });

    document.addEventListener('change', function (event) {
        const select = event.target.closest('[data-loan-status-select]');

        if (!select) {
            return;
        }

        submitLoanStatusSelect(select);
    });

    function initLoanLivePage() {
        syncLoanDueAt('loanBorrowedAt', 'loanDueAt');
        syncLoanDueAt('directLoanBorrowedAt', 'directLoanDueAt');
        syncRequestedLoanPanelSignature();
    }

    document.addEventListener('async:refreshed', function (event) {
        const selectors = event.detail?.selectors || [];

        if (selectors.includes('#loanPageWrap')) {
            syncLoanDueAt('loanBorrowedAt', 'loanDueAt');
            syncLoanDueAt('directLoanBorrowedAt', 'directLoanDueAt');
        }

        if (selectors.includes('#loanPageWrap') || selectors.includes('#loanStatsWrap')) {
            syncRequestedLoanPanelSignature();
        }
    });

    document.addEventListener('async:form-success', function () {
        syncRequestedLoanPanelSignature();
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            pollRequestedLoanPanel();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLoanLivePage, { once: true });
    } else {
        initLoanLivePage();
    }

    window.setInterval(function () {
        pollRequestedLoanPanel();
    }, 3000);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\admin\loans\index.blade.php ENDPATH**/ ?>