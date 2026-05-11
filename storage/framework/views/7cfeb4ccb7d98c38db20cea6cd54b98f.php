<?php $__env->startSection('content'); ?>
<?php ($title = 'Kelola Akun Pengguna'); ?>
<?php ($eyebrow = 'Khusus Superadmin'); ?>

<style>
    .account-shell{display:grid;grid-template-columns:minmax(0,1fr);gap:24px;width:100%}
    .account-add,.account-card{background:var(--dbx-card-bg, #fff);border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;overflow:hidden;box-shadow:none}
    .account-add{padding:24px;background:#ffffff;max-width:460px}
    .account-add:before,.account-add > *:before{content:none}
    .account-add-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:20px;font-weight:700;letter-spacing:0;color:var(--dbx-text, #1e293b);margin:0 0 8px 0}
    .account-card{padding:24px}
    .account-section-block{border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:20px;background:#fff}
    .account-section-title{font-size:12px;font-weight:700;color:var(--dbx-text-muted, #64748b);text-transform:uppercase;letter-spacing:.04em;margin-bottom:14px}
    .account-list-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--dbx-border, #e2e8f0)}
    .account-list-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:20px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .account-total-pill{font-size:12px;font-weight:600;color:var(--dbx-text-muted, #64748b);background:var(--dbx-bg, #f8fafc);padding:6px 12px;border-radius:999px;border:1px solid var(--dbx-border, #e2e8f0)}
    .account-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid var(--dbx-border, #e2e8f0)}
    .account-row:hover{background:#f8fafc}
    .account-row:first-child{border-top:none}
    .account-row:last-child{border-bottom:none}
    .account-row-main{display:flex;align-items:center;gap:14px;min-width:0}
    .account-avatar-chip{width:48px;height:48px;border-radius:12px;background:var(--dbx-primary-light, #fff7ed);color:var(--dbx-primary, #f97316);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;flex-shrink:0;box-shadow:none}
    .account-row-meta{min-width:0}
    .account-row-title{font-size:15px;font-weight:600;color:var(--dbx-text, #1e293b)}
    .account-row-sub{font-size:13px;color:var(--dbx-text-muted, #64748b);margin-top:2px}
    .account-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}

    .btn-account-glow{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;border-radius:8px;background:#fff;border:1px solid var(--dbx-border, #e2e8f0);font-size:14px;font-weight:600;color:var(--dbx-text, #1e293b);cursor:pointer;transition:all .2s;box-shadow:none}
    .btn-account-glow:hover{border-color:var(--dbx-primary, #f97316);color:var(--dbx-primary, #f97316);background:#fff;transform:none;box-shadow:none}
    .btn-account-glow.primary{background:var(--dbx-primary, #f97316);color:#fff;border-color:var(--dbx-primary, #f97316)}
    .btn-account-glow.primary:hover{background:var(--dbx-primary-hover, #ea580c);color:#fff;border-color:var(--dbx-primary-hover, #ea580c)}
    .btn-account-glow.danger:hover{border-color:var(--dbx-danger, #ef4444);color:var(--dbx-danger, #ef4444)}
    .account-empty{padding:56px 20px;text-align:center;color:var(--dbx-text-muted, #64748b)}
    .account-empty i{width:48px;height:48px;margin:0 auto 14px;opacity:.3}

    .report-usage-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px}
    .report-usage-widget{background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:4px;box-shadow:none}
    .report-usage-widget:hover{background:#fff;box-shadow:none}
    .report-usage-tag{font-size:12px;font-weight:600;text-transform:uppercase;color:var(--dbx-text-muted, #64748b);letter-spacing:0}
    .report-usage-number{font-size:28px;font-weight:800;color:var(--dbx-text, #1e293b);line-height:1}
    .report-usage-desc{font-size:12px;color:var(--dbx-text-muted, #64748b);font-weight:400}

    .member-toolbar{margin-bottom:24px;border-bottom:1px solid var(--dbx-border, #e2e8f0);padding-bottom:20px}
    .member-title{font-size:24px;font-weight:800}
    .member-subtitle{font-size:14px;color:var(--dbx-text-muted, #64748b);margin-top:4px}

    .account-add .form-input,.account-add .form-select,.account-modal .form-input,.account-modal .form-select{width:100%;padding:10px 14px!important;border:1px solid var(--dbx-border, #e2e8f0);border-radius:8px!important;background:#fff;font-size:14px;outline:none;transition:all .2s;box-shadow:none}
    .account-add .form-input:focus,.account-add .form-select:focus,.account-modal .form-input:focus,.account-modal .form-select:focus{border-color:var(--dbx-primary, #f97316);box-shadow:0 0 0 3px rgba(249,115,22,.1)}

    .drawer-mask{position:fixed;inset:0;background:rgba(0,0,0,.4);opacity:0;visibility:hidden;transition:all .3s;z-index:40}
    .drawer-mask.show{opacity:1;visibility:visible}
    .account-drawer{position:fixed;top:0;right:0;width:100%;max-width:480px;height:100vh;background:#fff;z-index:50;transform:translateX(100%);transition:transform .3s;display:flex;flex-direction:column;box-shadow:-10px 0 30px rgba(0,0,0,.1)}
    .account-drawer.open{transform:translateX(0)}
    .drawer-head{padding:20px 24px;border-bottom:1px solid var(--dbx-border, #e2e8f0);display:flex;justify-content:space-between;align-items:center;gap:12px}
    .drawer-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:20px;font-weight:700;color:var(--dbx-text, #1e293b)}
    .drawer-close{width:36px;height:36px;border-radius:8px;border:1px solid var(--dbx-border, #e2e8f0);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--dbx-text-muted, #64748b);transition:.2s}
    .drawer-close:hover{background:#fff;color:var(--dbx-primary, #f97316);border-color:var(--dbx-primary, #f97316)}
    .drawer-body{padding:24px;overflow-y:auto}

    .account-modal-mask{position:fixed;inset:0;background:rgba(15,23,42,.42);opacity:0;visibility:hidden;transition:all .25s ease;z-index:60}
    .account-modal-mask.show{opacity:1;visibility:visible}
    .account-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%) scale(.96);width:min(720px,calc(100vw - 24px));max-height:calc(100vh - 24px);overflow-y:auto;background:#fff;border:1px solid var(--dbx-border, #e2e8f0);border-radius:20px;box-shadow:0 24px 60px rgba(15,23,42,.18);z-index:70;opacity:0;pointer-events:none;transition:all .25s ease;padding:24px}
    .account-modal.show{opacity:1;pointer-events:auto;transform:translate(-50%,-50%) scale(1)}
    .account-modal-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px}
    .account-modal-title{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:700;color:var(--dbx-text, #1e293b);margin:0}
    .account-modal-sub{font-size:14px;color:var(--dbx-text-muted, #64748b);margin-top:4px}

    @media (max-width:1024px){.account-shell{grid-template-columns:1fr}.account-add{max-width:100%}}
    @media (max-width:768px){
        .account-row{flex-direction:column;align-items:flex-start}
        .account-actions{justify-content:flex-start}
        .report-usage-row,.grid.grid-cols-2{grid-template-columns:1fr}
        .account-modal{padding:18px}
        .account-modal-head{align-items:center}
    }
    @media (max-width:560px){
        .btn-account-glow{width:100%}
        .account-actions{width:100%}
    }
</style>

<div class="member-page">
    <div class="member-toolbar">
        <div>
            <h1 class="font-display member-title">Kelola Akun Pengguna</h1>
            <p class="member-subtitle">Manajemen akses sistem: tambah petugas baru, daftarkan anggota, dan atur status aktif akun.</p>
        </div>
        <div class="member-badge" style="background:var(--accent);color:#fff;padding:8px 16px;border-radius:999px;font-size:12px;font-weight:800;display:flex;align-items:center;gap:8px;"><i data-lucide="shield-check" class="w-4 h-4"></i> Akses superadmin</div>
    </div>

    <section id="usersStats" class="report-usage-row" style="margin-bottom: 32px;">
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--accent);">Total Akun</div>
            <div class="report-usage-number"><?php echo e($accountStats['total']); ?></div>
            <div class="report-usage-desc">Pengguna terdaftar</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--gold);">Staf & Admin</div>
            <div class="report-usage-number"><?php echo e($accountStats['petugas']); ?></div>
            <div class="report-usage-desc">Petugas aktif</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--teal);">Peminjam</div>
            <div class="report-usage-number"><?php echo e($accountStats['peminjam']); ?></div>
            <div class="report-usage-desc">Siswa & Guru</div>
        </div>
        <div class="report-usage-widget">
            <div class="report-usage-tag" style="color: var(--red);">Akun Aktif</div>
            <div class="report-usage-number"><?php echo e($accountStats['aktif']); ?></div>
            <div class="report-usage-desc">Status login aktif</div>
        </div>
    </section>

    <div class="account-shell">
        <div class="account-add">
            <h2 class="account-add-title">Import & Backup Pengguna</h2>
            <p class="text-sm text-slate-500 mt-2 mb-8">Kelola data massal pengguna lewat file CSV.</p>

            <div class="account-section-block">
                <div class="account-section-title">Import & Backup Pengguna</div>
                <form method="POST" action="<?php echo e(route('admin.users.import')); ?>" enctype="multipart/form-data" class="space-y-3" data-async="true" data-reset-on-success="true" data-refresh-targets="#usersStats,#accountList">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="import_file" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" accept=".csv,text/csv" required>
                    <button type="submit" class="btn-account-glow w-full">Import CSV Pengguna</button>
                </form>
                <a href="<?php echo e(route('admin.users.export')); ?>" class="btn-account-glow w-full" style="display:flex;margin-top:12px;">Backup CSV Pengguna</a>
            </div>
        </div>

        <div id="accountList" class="account-card">
            <div class="account-list-head">
                <div>
                    <h2 class="account-list-title">Daftar Pengguna</h2>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="account-total-pill">
                        <?php echo e($users->total()); ?> Total
                    </div>
                    <button type="button" onclick="openCreateUserModal()" class="btn-account-glow primary">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah User
                    </button>
                </div>
            </div>

            <div class="flex flex-col">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="account-row">
                        <div class="account-row-main">
                            <div class="account-avatar-chip">
                                <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                            </div>
                            <div class="account-row-meta">
                                <div class="account-row-title"><?php echo e($user->name); ?></div>
                                <div class="account-row-sub">@<span><?php echo e($user->username); ?></span> &bull; <?php echo e($user->role?->name); ?></div>
                            </div>
                        </div>
                        <div class="account-actions">
                            <button type="button" onclick="openEditDrawer(<?php echo e($user->id); ?>)" class="btn-account-glow">
                                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                            </button>
                            <form method="POST" action="<?php echo e(route('admin.users.destroy', $user)); ?>" data-async="true" data-confirm="Hapus akun ini?" data-remove-closest=".account-row" data-refresh-targets="#usersStats,#accountList">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn-account-glow danger">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="account-empty">
                        <i data-lucide="users"></i>
                        <p>Belum ada data pengguna.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-8">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
</div>

<div id="createUserModalMask" class="account-modal-mask" onclick="closeCreateUserModal()"></div>
<div id="createUserModal" class="account-modal" aria-hidden="true">
    <div class="account-modal-head">
        <div>
            <h2 class="account-modal-title">Tambah User</h2>
            <p class="account-modal-sub">Isi data akun baru, lalu simpan untuk menambahkan pengguna ke sistem.</p>
        </div>
        <button type="button" onclick="closeCreateUserModal()" class="drawer-close">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <form id="createUserForm" method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="space-y-5" data-async="true" data-reset-on-success="true" data-success-call="closeCreateUserModal" data-refresh-targets="#usersStats,#accountList">
        <?php echo csrf_field(); ?>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
            <input name="name" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Nama lengkap..." required>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
            <input type="email" name="email" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Email aktif..." required>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Username</label>
                <input name="username" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Username..." required>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Password</label>
                <input type="password" name="password" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Minimal 5 karakter" required>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telepon</label>
                <input name="phone" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="No. HP...">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Akun</label>
                <select name="is_active" class="form-select w-full px-4 py-3.5 text-sm rounded-xl">
                    <option value="1">Aktif</option>
                    <option value="0">Non-aktif</option>
                </select>
            </div>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peran (Role)</label>
            <select name="role_id" class="form-select w-full px-4 py-3.5 text-sm rounded-xl" required onchange="toggleAcademicFields(this, '#academicFieldsStore')">
                <option value="">Pilih Role...</option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($role->id); ?>" data-name="<?php echo e($role->name); ?>"><?php echo e($role->label ?: $role->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div id="academicFieldsStore" style="display: none;" class="grid grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas</label>
                <input name="kelas" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Contoh: XII RPL 1">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jurusan</label>
                <input name="jurusan" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Contoh: Rekayasa Perangkat Lunak">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-2 flex-wrap">
            <button type="button" onclick="closeCreateUserModal()" class="btn-account-glow">Batal</button>
            <button type="submit" class="btn-account-glow primary">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Simpan Pengguna
            </button>
        </div>
    </form>
</div>

<div id="drawerMask" class="drawer-mask" onclick="closeEditDrawer()"></div>
<div id="editDrawer" class="account-drawer">
    <div class="drawer-head">
        <div>
            <h2 class="drawer-title">Edit Akun</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi profil atau ganti password.</p>
        </div>
        <button type="button" onclick="closeEditDrawer()" class="drawer-close">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    <div id="drawerBody" class="drawer-body">
        <div class="flex items-center justify-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-800"></div>
        </div>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('createUserModalMask').classList.add('show');
        document.getElementById('createUserModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateUserModal() {
        const form = document.getElementById('createUserForm');
        const academicFields = document.getElementById('academicFieldsStore');

        document.getElementById('createUserModalMask').classList.remove('show');
        document.getElementById('createUserModal').classList.remove('show');
        document.body.style.overflow = '';

        if (form) {
            form.reset();
        }

        if (academicFields) {
            academicFields.style.display = 'none';
        }
    }

    function openEditDrawer(userId) {
        const drawer = document.getElementById('editDrawer');
        const mask = document.getElementById('drawerMask');
        const body = document.getElementById('drawerBody');

        mask.classList.add('show');
        drawer.classList.add('open');

        fetch(`/admin/users/${userId}/edit`)
            .then(res => res.text())
            .then(html => {
                body.innerHTML = html;
                lucide.createIcons();
            });
    }

    function closeEditDrawer() {
        document.getElementById('editDrawer').classList.remove('open');
        document.getElementById('drawerMask').classList.remove('show');
    }

    function toggleAcademicFields(select, targetId) {
        const selectedOption = select.options[select.selectedIndex];
        const roleName = selectedOption.getAttribute('data-name');
        const target = document.querySelector(targetId);

        if (!target) {
            return;
        }

        if (roleName === 'siswa' || roleName === 'guru') {
            target.style.display = 'grid';
        } else {
            target.style.display = 'none';
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCreateUserModal();
            closeEditDrawer();
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/users/index.blade.php ENDPATH**/ ?>