<?php $__env->startSection('content'); ?>
<?php ($title = 'Backup & Restore Data'); ?>
<?php ($eyebrow = 'Keamanan Sistem'); ?>
<?php ($totalBackupSizeKb = number_format((int) $backups->sum('size_bytes') / 1024, 0)); ?>
<?php ($latestBackupLabel = $backups->first()?->created_at?->diffForHumans() ?? '-'); ?>

<style>
    .backup-page{display:flex;flex-direction:column;gap:24px}
    .backup-shell{display:grid;grid-template-columns:1fr 2fr;gap:24px}
    .backup-card{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:24px;box-shadow:none}
    .backup-card-title{font-size:18px;font-weight:700;color:var(--dbx-text, #1e293b);margin:0 0 4px}
    .backup-card-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin:0 0 16px;line-height:1.6}
    .backup-note{margin-top:20px;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid var(--dbx-border, #e2e8f0);font-size:12px;color:#64748b;line-height:1.7}
    .backup-note strong{color:#1e293b}
    .backup-list-card{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:24px;box-shadow:none}
    .backup-list-title{font-size:18px;font-weight:700;color:var(--dbx-text, #1e293b);margin:0 0 16px}
    .backup-item{border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;padding:16px;margin-bottom:12px}
    .backup-item:last-child{margin-bottom:0}
    .backup-item:hover{background:#f8fafc}
    .backup-item-title{font-size:14px;font-weight:600;color:var(--dbx-text, #1e293b)}
    .backup-item-path,.backup-item-meta{font-size:12px;color:#94a3b8}
    .backup-item-path{margin-top:4px;word-break:break-all}
    .backup-item-meta{margin-top:6px}
    .backup-item-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
    .backup-size-badge{display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;min-height:28px;border-radius:999px;background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);font-size:11px;font-weight:600;line-height:1}
    .btn-backup-primary{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--dbx-primary, #f97316);color:#fff;border:1px solid var(--dbx-primary, #f97316);border-radius:8px;padding:10px 20px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none}
    .btn-backup-primary:hover{background:var(--dbx-primary-hover, #ea580c);border-color:var(--dbx-primary-hover, #ea580c);color:#fff}
    .btn-backup-secondary{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:var(--dbx-text-muted, #64748b);border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;padding:10px 20px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none}
    .btn-backup-secondary:hover{border-color:var(--dbx-primary, #f97316);color:var(--dbx-primary, #f97316)}
    .backup-empty{border:1px dashed var(--dbx-border, #e2e8f0);border-radius:8px;padding:20px;font-size:14px;color:#94a3b8;background:#fff}
    @media (max-width:1024px){.backup-shell{grid-template-columns:1fr}}
</style>

<div class="member-page backup-page">
    <div class="member-toolbar">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
            <div>
                <h1 class="font-display member-title">Backup & Restore Data</h1>
                <p class="member-subtitle" style="margin-top:4px;">Buat snapshot data dan restore database dengan mudah.</p>
            </div>
            <div class="member-badge"><i data-lucide="database-backup" class="w-3.5 h-3.5"></i> <?php echo e($backups->total()); ?> backup tersimpan</div>
        </div>
    </div>

    <section id="backupStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);"><i data-lucide="database" class="w-5 h-5"></i></div>
            <div><div class="member-mini-value"><?php echo e($backups->total()); ?></div><div class="member-mini-label">Total Backup</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:#fef3c7;color:#eab308;"><i data-lucide="hard-drive-download" class="w-5 h-5"></i></div>
            <div><div class="member-mini-value"><?php echo e($totalBackupSizeKb); ?> KB</div><div class="member-mini-label">Ukuran Total</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:#dcfce7;color:#22c55e;"><i data-lucide="clock-3" class="w-5 h-5"></i></div>
            <div><div class="member-mini-value"><?php echo e($latestBackupLabel); ?></div><div class="member-mini-label">Backup Terakhir</div></div>
        </div>
    </section>

    <div class="backup-shell">
        <div class="backup-card">
            <h3 class="backup-card-title">Buat Backup Baru</h3>
            <p class="backup-card-sub">Snapshot data akan disimpan dalam format JSON dan bisa diunduh sebagai SQL database.</p>

            <form method="POST" action="<?php echo e(route('admin.backups.store')); ?>" data-async="true" data-refresh-targets="#backupStats,#backupList">
                <?php echo csrf_field(); ?>
                <button class="btn-backup-primary" style="width:100%;" type="submit">
                    <i data-lucide="plus" class="w-4 h-4"></i> Buat Backup Sekarang
                </button>
            </form>

            <div class="backup-note">
                <strong>Info:</strong> Restore database memakai mode <em>update/merge</em>, sehingga data lama tidak dihapus total.
            </div>
        </div>

        <div id="backupList" class="backup-list-card">
            <h3 class="backup-list-title">Riwayat Backup</h3>
            <div>
                <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="backup-item">
                        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                            <div>
                                <div class="backup-item-title"><?php echo e($backup->file_name); ?></div>
                                <div class="backup-item-path"><?php echo e($backup->file_path); ?></div>
                                <div class="backup-item-meta">
                                    Dibuat <?php echo e($backup->created_at?->translatedFormat('d M Y H:i') ?? '-'); ?>

                                    | Oleh <?php echo e($backup->creator?->name ?? 'Sistem'); ?>

                                </div>
                            </div>
                            <div class="backup-size-badge">
                                <?php echo e(number_format(((int) $backup->size_bytes) / 1024, 1)); ?> KB
                            </div>
                        </div>
                        <div class="backup-item-actions">
                            <a href="<?php echo e(route('admin.backups.download', $backup)); ?>" class="btn-backup-secondary">
                                <i data-lucide="download" class="w-4 h-4"></i> Unduh SQL
                            </a>
                            <form method="POST" action="<?php echo e(route('admin.backups.restore', $backup)); ?>" data-async="true" data-confirm="Restore backup ini ke database? Sistem akan update/merge data, bukan replace total." data-refresh-targets="#backupStats,#backupList">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-backup-primary">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Restore
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="backup-empty">
                        Belum ada backup. Tekan tombol di kiri untuk membuat snapshot pertama.
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:16px;"><?php echo e($backups->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/backups/index.blade.php ENDPATH**/ ?>