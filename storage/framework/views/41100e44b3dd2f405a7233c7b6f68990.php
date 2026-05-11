<div class="loan-card">
    <div class="loan-card-header">
        <div>
            <h2 class="loan-card-title">Pengajuan</h2>
            <p class="loan-card-subtitle">Pengajuan dari akun peminjam.</p>
        </div>
        <span class="loan-item-badge pending" id="loanRequestedBadge"><?php echo e($requestedLoans->count()); ?> Menunggu</span>
    </div>
    <div class="loan-card-body">
        <div class="flex flex-col gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $requestedLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requestedLoan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="loan-item-card">
                    <div class="loan-item-head">
                        <div>
                            <div class="loan-item-name"><?php echo e($requestedLoan->member?->name ?? 'Peminjam'); ?></div>
                            <div class="loan-item-info mt-1">
                                <strong><?php echo e($requestedLoan->book?->title ?? 'Buku'); ?></strong>
                            </div>
                        </div>
                        <span class="loan-item-badge pending">Sistem</span>
                    </div>
                    <div class="loan-item-info">
                        Pinjam: <?php echo e(optional($requestedLoan->borrowed_at)->translatedFormat('d M Y')); ?><br>
                        Batas: <?php echo e(optional($requestedLoan->due_at)->translatedFormat('d M Y')); ?>

                    </div>
                    <?php if($requestedLoan->notes): ?>
                        <div class="p-3 rounded-xl bg-white border border-slate2-100 text-xs italic text-slate2-500">
                            "<?php echo e($requestedLoan->notes); ?>"
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('admin.loans.update', $requestedLoan)); ?>" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="status" value="borrowed">
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="book-check" class="w-4 h-4"></i> Proses Sekarang
                        </button>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="report-empty-state" style="padding: 40px 20px;">
                    <div class="report-empty-icon"><i data-lucide="inbox"></i></div>
                    <p class="report-empty-text">Belum ada pengajuan baru.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/loans/_requested-panel.blade.php ENDPATH**/ ?>