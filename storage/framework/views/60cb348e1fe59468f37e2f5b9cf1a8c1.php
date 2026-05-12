<?php $__env->startSection('heading', 'Login OTP'); ?>
<?php $__env->startSection('subheading', 'Masukkan email atau username untuk menerima kode OTP'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $otpLoginEmail = $otpLoginUser?->email ?? '';
        $otpMaskedEmail = '';

        if ($otpLoginEmail && str_contains($otpLoginEmail, '@')) {
            [$otpName, $otpDomain] = explode('@', $otpLoginEmail, 2);
            $otpVisibleName = substr($otpName, 0, min(strlen($otpName), 2));
            $otpMaskedEmail = $otpVisibleName.str_repeat('*', max(strlen($otpName) - strlen($otpVisibleName), 1)).'@'.$otpDomain;
        }
    ?>

    <form method="POST" action="<?php echo e(route('login.otp.send')); ?>" class="space-y-4" data-async-auth="true" data-loading-label="Mengirim OTP...">
        <?php echo csrf_field(); ?>

        <div>
            <label for="identifier" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">email / username</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                value="<?php echo e(old('identifier', $otpLoginUser?->username ?? '')); ?>"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="masukkan email atau username"
                autocomplete="username"
                required
            >
            <p data-error-for="identifier" class="hidden text-xs text-red-600 mt-1.5"></p>
            <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-xs text-red-600 mt-1.5"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Kirim Token OTP
        </button>
    </form>

    <?php if($otpLoginUser): ?>
        <div class="mt-5 rounded-2xl border border-forest-200 bg-forest-50 px-4 py-3 text-xs text-forest-700">
            Token OTP sudah dikirim ke <?php echo e($otpMaskedEmail ?: $otpLoginUser->email); ?>.
        </div>

        <form method="POST" action="<?php echo e(route('login.otp.verify')); ?>" class="space-y-4 mt-5" data-async-auth="true" data-loading-label="Memverifikasi OTP...">
            <?php echo csrf_field(); ?>

            <div>
                <label for="otp" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">kode otp</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    inputmode="numeric"
                    class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950 tracking-[0.35em]"
                    placeholder="6 digit kode"
                    required
                >
                <p data-error-for="otp" class="hidden text-xs text-red-600 mt-1.5"></p>
                <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-600 mt-1.5"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl border border-lib-300 bg-white px-4 py-3.5 text-sm font-semibold text-lib-900 transition hover:border-lib-400 hover:bg-lib-100">
                Verifikasi OTP dan Masuk
            </button>
        </form>
    <?php endif; ?>

    <p class="text-center mt-7 text-sm text-lib-700/60">
        Kembali ke login password?
        <a href="<?php echo e(route('login')); ?>" class="auth-link">Masuk biasa</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views\auth\login-otp.blade.php ENDPATH**/ ?>