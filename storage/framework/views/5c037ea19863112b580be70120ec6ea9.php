<?php $__env->startSection('content'); ?>
<?php ($title = 'Restore Data'); ?>
<?php ($eyebrow = 'Khusus Superadmin'); ?>

<style>
    .restore-head{border-bottom:1px solid var(--dbx-border, #e2e8f0);padding-bottom:24px;margin-bottom:24px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px}
    .restore-title{font-size:24px;font-weight:800;color:var(--dbx-text, #1e293b)}
    .restore-subtitle{font-size:14px;color:var(--dbx-text-muted, #64748b);margin-top:4px;line-height:1.6}
    .restore-badge{background:var(--dbx-primary, #f97316);color:#fff;padding:8px 16px;border-radius:999px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:8px}
    .restore-group{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;overflow:hidden;margin-bottom:20px}
    .restore-group-head{background:var(--dbx-primary-light, #fff7ed);border-bottom:1px solid var(--dbx-border, #e2e8f0);padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
    .restore-group-title{font-size:16px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .restore-count{font-size:12px;font-weight:600;background:#fff;border:1px solid rgba(249,115,22,.2);padding:4px 10px;border-radius:999px;color:var(--dbx-primary, #f97316)}
    .restore-row{display:grid;grid-template-columns:1.5fr 1fr 0.8fr 0.8fr 0.8fr 0.8fr 1fr;gap:16px;align-items:center;padding:16px 24px;border-bottom:1px solid var(--dbx-border, #e2e8f0)}
    .restore-row:last-child{border-bottom:none}
    .restore-row:hover{background:#f8fafc}
    .restore-meta-label{font-size:11px;font-weight:600;text-transform:uppercase;color:#94a3b8;margin-bottom:2px}
    .restore-name{font-size:14px;font-weight:600;color:var(--dbx-text, #1e293b)}
    .restore-meta-value{font-size:13px;color:var(--dbx-text-muted, #64748b)}
    .restore-actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}
    .restore-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;border:1px solid transparent}
    .restore-btn-primary{background:var(--dbx-primary, #f97316);color:#fff}
    .restore-btn-primary:hover{background:var(--dbx-primary-hover, #ea580c)}
    .restore-btn-danger{background:var(--dbx-danger-light, #fee2e2);color:var(--dbx-danger, #ef4444)}
    .restore-btn-danger:hover{background:var(--dbx-danger, #ef4444);color:#fff}
    .restore-empty{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:48px 24px;text-align:center;color:var(--dbx-text-muted, #64748b)}
    .restore-empty i{width:44px;height:44px;margin:0 auto 14px;opacity:.35}
    @media (max-width:1024px){.restore-row{grid-template-columns:1fr 1fr}.restore-actions{grid-column:1 / -1;justify-content:flex-start;margin-top:8px}}
    @media (max-width:640px){.restore-row{grid-template-columns:1fr}}
</style>

<div class="member-page">
    <div class="restore-head">
        <div>
            <h1 class="font-display restore-title">Restore Data</h1>
            <p class="restore-subtitle">Data yang telah dihapus sementara dapat dikembalikan atau dihapus permanen dari sini.</p>
        </div>
        <div class="restore-badge"><i data-lucide="archive-restore" class="w-4 h-4"></i> <?php echo e($deletedTotal); ?> Data Terhapus</div>
    </div>

    <div id="restoreList">
        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <section class="restore-group">
                <div class="restore-group-head">
                    <div class="restore-group-title"><?php echo e($group['label']); ?></div>
                    <div class="restore-count"><?php echo e($group['count']); ?> data</div>
                </div>
                <div>
                    <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="restore-row">
                            <div>
                                <div class="restore-meta-label"><?php echo e($item['label']); ?></div>
                                <div class="restore-name"><?php echo e($item['name']); ?></div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Dihapus oleh</div>
                                <div class="restore-meta-value"><?php echo e($item['deleted_by']); ?></div>
                            </div>
                            <div>
                                <div class="restore-meta-label">IP Address</div>
                                <div class="restore-meta-value"><?php echo e($item['deleted_ip']); ?></div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Hari</div>
                                <div class="restore-meta-value"><?php echo e($item['deleted_day']); ?></div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Tanggal</div>
                                <div class="restore-meta-value"><?php echo e($item['deleted_date']); ?></div>
                            </div>
                            <div>
                                <div class="restore-meta-label">Jam</div>
                                <div class="restore-meta-value"><?php echo e($item['deleted_time']); ?></div>
                            </div>
                            <div class="restore-actions">
                                <form method="POST" action="<?php echo e(route('admin.restore.restore', [$item['table'], $item['id']])); ?>" data-async="true" data-confirm="Kembalikan data ini?" data-remove-closest=".restore-row">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="restore-btn restore-btn-primary">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Restore
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo e(route('admin.restore.force-delete', [$item['table'], $item['id']])); ?>" data-async="true" data-confirm="Hapus permanen data ini? Tindakan ini tidak bisa dibatalkan." data-remove-closest=".restore-row">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="restore-btn restore-btn-danger">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="restore-empty">
                <i data-lucide="archive-restore"></i>
                <p>Tidak ada data yang sedang terhapus.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/restore/index.blade.php ENDPATH**/ ?>