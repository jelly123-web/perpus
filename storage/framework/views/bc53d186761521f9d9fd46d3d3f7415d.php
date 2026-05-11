<?php $__env->startSection('content'); ?>
<?php ($title = 'Table Access'); ?>
<?php ($eyebrow = 'Role dan Permission'); ?>
<?php ($visiblePermissions = $permissions->reject(fn ($permission) => $permission->name === 'scan_books')->values()); ?>

<style>
    .access-page{display:flex;flex-direction:column;gap:24px}
    .access-shell{display:grid;grid-template-columns:1.5fr 1fr;gap:24px}
    .access-card{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;overflow:hidden;box-shadow:none}
    .access-card-head{padding:20px 24px;border-bottom:1px solid var(--dbx-border, #e2e8f0);background:#fafafa;display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
    .access-card-body{padding:24px}
    .access-card-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:700;color:var(--dbx-text, #1e293b);margin:0}
    .access-card-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:4px;line-height:1.5}
    .access-role-pill{padding:6px 12px;border-radius:6px;background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px}

    .access-table-wrap{overflow-x:auto;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;background:#fff}
    .access-table{width:100%;border-collapse:collapse;min-width:600px}
    .access-table th{background:var(--dbx-bg, #f8fafc);padding:12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;color:var(--dbx-text-muted, #64748b);border-bottom:1px solid var(--dbx-border, #e2e8f0);letter-spacing:0}
    .access-table td{padding:12px;border-bottom:1px solid var(--dbx-border, #e2e8f0);font-size:14px;vertical-align:middle;color:var(--dbx-text, #1e293b)}
    .access-table tbody tr:last-child td{border-bottom:none}
    .access-table tbody tr:hover{background:#f8fafc}
    .access-permission-title{font-size:14px;font-weight:600;color:var(--dbx-text, #1e293b)}
    .access-permission-name{font-size:12px;color:#94a3b8;margin-top:2px;font-family:monospace}
    .access-check{text-align:center}
    .access-check input{
        width:20px;
        height:20px;
        cursor:pointer;
        appearance:none;
        -webkit-appearance:none;
        border:1px solid var(--dbx-border, #e2e8f0);
        border-radius:6px;
        background:#fff;
        display:inline-grid;
        place-content:center;
        transition:all .2s;
    }
    .access-check input::after{
        content:"";
        width:10px;
        height:6px;
        border:2px solid #fff;
        border-top:0;
        border-right:0;
        transform:rotate(-45deg) scale(0);
        transform-origin:center;
        transition:transform .15s ease-in-out;
        margin-top:-1px;
    }
    .access-check input:checked{
        background:var(--dbx-primary, #f97316);
        border-color:var(--dbx-primary, #f97316);
    }
    .access-check input:checked::after{
        transform:rotate(-45deg) scale(1);
    }
    .access-check input:focus{
        outline:none;
        box-shadow:0 0 0 3px rgba(249,115,22,.12);
    }
    .access-role-head{min-width:130px}
    .access-role-label{font-size:13px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .access-role-sub{font-size:11px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    .access-actions{display:flex;justify-content:flex-end;margin-top:20px;padding-top:20px;border-top:1px solid var(--dbx-border, #e2e8f0)}

    .access-summary{display:flex;flex-direction:column;gap:20px}
    .access-summary-item{display:flex;justify-content:space-between;align-items:center;padding:16px;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;background:#fff}
    .access-summary-label{font-size:13px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .access-summary-sub{font-size:12px;color:var(--dbx-text-muted, #64748b);margin-top:4px}
    .access-summary-value{font-size:24px;font-weight:800;color:var(--dbx-primary, #f97316)}
    .access-role-list{display:flex;flex-direction:column;gap:12px}
    .access-role-item{padding:16px;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px;background:#fff}
    .access-role-item-head{display:flex;justify-content:space-between;align-items:center;gap:12px}
    .access-role-item-title{font-size:14px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .access-role-item-sub{font-size:12px;color:var(--dbx-text-muted, #64748b);margin-top:2px}
    .access-role-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
    .access-role-meta-box{background:var(--dbx-bg, #f8fafc);padding:10px;border-radius:6px}
    .access-role-meta-label{font-size:10px;text-transform:uppercase;color:var(--dbx-text-muted, #64748b);letter-spacing:0}
    .access-role-meta-value{font-size:16px;font-weight:700;margin-top:4px;color:var(--dbx-text, #1e293b)}

    .member-mini-stats{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px}
    .member-mini-stat{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:20px;display:flex;align-items:center;gap:16px}
    .member-mini-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;background:var(--dbx-primary, #f97316)}
    .member-mini-value{font-size:24px;font-weight:800;color:var(--dbx-text, #1e293b)}
    .member-mini-label{font-size:12px;color:var(--dbx-text-muted, #64748b)}

    @media (max-width:1024px){.access-shell{grid-template-columns:1fr}}
</style>

<div class="access-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Table Access</h1>
            <p class="member-subtitle">Checklist hak akses tiap role dalam satu tabel. Semua perubahan disimpan ke database pivot permission.</p>
        </div>
        <div class="member-badge"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> <?php echo e($roles->count()); ?> role aktif</div>
    </div>

    <section id="roleStats" class="member-mini-stats">
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);"><i data-lucide="shield-check" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value"><?php echo e($roles->count()); ?></div><div class="member-mini-label">Total Role</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--gold-light);color:var(--gold);"><i data-lucide="key-round" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value"><?php echo e($visiblePermissions->count()); ?></div><div class="member-mini-label">Permission Tersedia</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--teal-light);color:var(--teal);"><i data-lucide="users-round" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value"><?php echo e($roles->sum(fn($role) => $role->users->count())); ?></div><div class="member-mini-label">User Dalam Semua Role</div></div>
        </div>
        <div class="member-mini-stat">
            <div class="member-mini-icon" style="background:var(--orange-light);color:var(--orange);"><i data-lucide="list-checks" class="w-4 h-4"></i></div>
            <div><div class="member-mini-value"><?php echo e($roles->sum(fn($role) => $role->permissions->where('name', '!=', 'scan_books')->count())); ?></div><div class="member-mini-label">Permission Terpasang</div></div>
        </div>
    </section>

    <div id="roleAccessShell" class="access-shell">
        <form method="POST" action="<?php echo e(route('admin.roles.matrix.update')); ?>" class="access-card" data-async="true" data-refresh-targets="#roleStats,#roleAccessShell">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="access-card-head">
                <div>
                    <h2 class="access-card-title">Checklist Akses</h2>
                    <p class="access-card-sub">Centang permission yang boleh dilakukan role terkait. Misalnya admin bisa kelola user, sedangkan siswa hanya memakai fitur umum tanpa akses admin.</p>
                </div>
                <div class="access-role-pill"><i data-lucide="database" class="w-3.5 h-3.5"></i> Sinkron ke database</div>
            </div>

            <div class="access-card-body">
                <div class="access-table-wrap">
                    <table class="access-table">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="access-role-head">
                                        <div class="access-role-label"><?php echo e($role->label); ?></div>
                                        <div class="access-role-sub"><?php echo e($role->name); ?></div>
                                    </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $visiblePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="access-permission-title"><?php echo e($permission->label); ?></div>
                                        <div class="access-permission-name"><?php echo e($permission->name); ?></div>
                                    </td>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="access-check">
                                            <input
                                                type="checkbox"
                                                name="permissions[<?php echo e($role->id); ?>][]"
                                                value="<?php echo e($permission->id); ?>"
                                                <?php if($role->permissions->contains($permission->id)): echo 'checked'; endif; ?>
                                                aria-label="<?php echo e($role->label); ?> - <?php echo e($permission->label); ?>"
                                            >
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="access-actions">
                    <button class="btn-primary rounded-xl px-5 py-3 text-sm font-semibold" type="submit">
                        <i data-lucide="save" class="w-4 h-4"></i>Simpan Table Access
                    </button>
                </div>
            </div>
        </form>

        <div class="access-summary">
            <div class="access-card">
                <div class="access-card-head">
                    <div>
                        <h2 class="access-card-title">Ringkasan</h2>
                        <p class="access-card-sub">Panel cepat untuk membaca kondisi role dan distribusi akses saat ini.</p>
                    </div>
                </div>
                <div class="access-card-body">
                    <div class="access-summary-item">
                        <div>
                            <div class="access-summary-label">Role dengan akses admin</div>
                            <div class="access-summary-sub">Role yang punya minimal satu permission panel admin.</div>
                        </div>
                        <div class="access-summary-value"><?php echo e($roles->filter(fn($role) => $role->permissions->isNotEmpty())->count()); ?></div>
                    </div>
                    <div class="access-summary-item">
                        <div>
                            <div class="access-summary-label">Role tanpa akses panel</div>
                            <div class="access-summary-sub">Cocok untuk user umum seperti siswa atau guru.</div>
                        </div>
                        <div class="access-summary-value"><?php echo e($roles->filter(fn($role) => $role->permissions->isEmpty())->count()); ?></div>
                    </div>
                </div>
            </div>

            <div class="access-card">
                <div class="access-card-head">
                    <div>
                        <h2 class="access-card-title">Role Aktif</h2>
                        <p class="access-card-sub">Checklist di tabel kiri langsung mengikuti daftar role dari database.</p>
                    </div>
                </div>
                <div class="access-card-body">
                    <div class="access-role-list">
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="access-role-item">
                                <div class="access-role-item-head">
                                    <div>
                                        <div class="access-role-item-title"><?php echo e($role->label); ?></div>
                                        <div class="access-role-item-sub"><?php echo e($role->name); ?></div>
                                    </div>
                                    <div class="access-role-pill"><?php echo e($role->permissions->where('name', '!=', 'scan_books')->count()); ?> akses</div>
                                </div>
                                <div class="access-role-meta">
                                    <div class="access-role-meta-box">
                                        <div class="access-role-meta-label">Jumlah User</div>
                                        <div class="access-role-meta-value"><?php echo e($role->users->count()); ?></div>
                                    </div>
                                    <div class="access-role-meta-box">
                                        <div class="access-role-meta-label">Permission</div>
                                        <div class="access-role-meta-value"><?php echo e($role->permissions->where('name', '!=', 'scan_books')->count()); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>