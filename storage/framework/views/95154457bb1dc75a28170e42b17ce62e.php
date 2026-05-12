<?php $__env->startSection('heading', 'Verifikasi Email'); ?>
<?php $__env->startSection('subheading', 'Buka email Anda lalu klik link verifikasi untuk mengaktifkan akun'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-5">
        <div class="rounded-2xl border border-lib-100 bg-lib-50/70 p-5">
            <h3 class="text-sm font-semibold text-lib-900">Cek Inbox Email</h3>
            <p class="mt-2 text-sm text-lib-700/80 leading-6">
                Kami sudah mengirim link verifikasi ke <span class="font-semibold text-lib-900"><?php echo e(auth()->user()?->email); ?></span>.
                Klik link di email itu untuk menyelesaikan pendaftaran akun.
            </p>
            <p class="mt-3 text-xs text-lib-700/70 leading-5">
                Kalau email belum masuk, cek folder spam atau kirim ulang link verifikasi.
            </p>
        </div>

        <form method="POST" action="<?php echo e(route('verification.send')); ?>" data-async-auth="true" data-loading-label="Mengirim ulang...">
            <?php echo csrf_field(); ?>
            <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
                Kirim Ulang Link Verifikasi
            </button>
        </form>

        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="w-full rounded-xl border border-lib-200 bg-white px-4 py-3 text-sm font-semibold text-lib-900 transition hover:border-lib-300 hover:bg-lib-50">
                Logout
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>