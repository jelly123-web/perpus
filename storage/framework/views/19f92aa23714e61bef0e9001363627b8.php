<form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>" class="space-y-6" data-async="true" data-refresh-targets="#usersStats,#accountList" data-success-call="closeEditDrawer">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    
    <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
        <input name="name" value="<?php echo e($user->name); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Nama lengkap..." required>
    </div>
    
    <div class="space-y-1.5">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
        <input type="email" name="email" value="<?php echo e($user->email); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Email aktif..." required>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Username</label>
            <input name="username" value="<?php echo e($user->username); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Username..." required>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telepon</label>
            <input name="phone" value="<?php echo e($user->phone); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="No. HP/WA...">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peran (Role)</label>
            <select name="role_id" class="form-select w-full px-4 py-3.5 text-sm rounded-xl" required onchange="toggleAcademicFields(this, '#academicFieldsEdit')">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($role->id); ?>" data-name="<?php echo e($role->name); ?>" <?php echo e($user->role_id == $role->id ? 'selected' : ''); ?>><?php echo e($role->label ?: $role->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Akun</label>
            <select name="is_active" class="form-select w-full px-4 py-3.5 text-sm rounded-xl">
                <option value="1" <?php echo e($user->is_active ? 'selected' : ''); ?>>Aktif</option>
                <option value="0" <?php echo e(!$user->is_active ? 'selected' : ''); ?>>Non-aktif</option>
            </select>
        </div>
    </div>

    <div id="academicFieldsEdit" style="<?php echo e(in_array($user->role?->name, ['siswa', 'guru']) ? 'display: grid;' : 'display: none;'); ?>" class="grid grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas</label>
            <input name="kelas" value="<?php echo e($user->kelas); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Contoh: XII RPL 1">
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jurusan</label>
            <input name="jurusan" value="<?php echo e($user->jurusan); ?>" class="form-input w-full px-4 py-3.5 text-sm rounded-xl" placeholder="Contoh: Rekayasa Perangkat Lunak">
        </div>
    </div>

    <div style="background:var(--bg-soft);padding:20px;border-radius:18px;border:1px solid var(--border-light);">
        <div class="flex items-center gap-3 mb-3">
            <div style="background:var(--accent-glow);color:var(--accent);padding:8px;border-radius:10px;">
                <i data-lucide="key" class="w-4 h-4"></i>
            </div>
            <div class="text-sm font-bold text-slate-700">Ganti Password</div>
        </div>
        <input type="password" name="password" class="form-input w-full px-4 py-3 text-sm rounded-xl" placeholder="Isi hanya jika ingin mengganti password...">
        <p class="text-[11px] text-slate-400 mt-2">Kosongkan jika tidak ingin mengubah password.</p>
    </div>

    <div class="pt-4 flex gap-3">
        <button type="button" onclick="closeEditDrawer()" class="btn-account-glow flex-1">Batal</button>
        <button type="submit" class="btn-account-glow primary flex-[2] py-4 rounded-xl font-bold">
            <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
        </button>
    </div>
</form>
<?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/admin/users/edit.blade.php ENDPATH**/ ?>