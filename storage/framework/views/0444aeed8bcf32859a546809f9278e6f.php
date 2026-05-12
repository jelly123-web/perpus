<?php $__env->startSection('heading', 'Buat Akun'); ?>
<?php $__env->startSection('subheading', 'Daftarkan akun baru untuk masuk ke perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .register-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .register-field {
            text-align: left;
        }

        .register-label {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #1E293B;
        }

        .register-input-wrap {
            position: relative;
        }

        .register-input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .register-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border-radius: 12px;
            font-size: 15px;
            line-height: 1.4;
        }

        .register-input.with-toggle {
            padding-right: 48px;
        }

        .register-input:focus + .register-input-icon,
        .register-input-wrap:focus-within .register-input-icon {
            color: #F97316;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #F97316;
        }

        .register-recaptcha {
            border-radius: 12px;
            background: #F1F5F9;
            padding: 12px;
            overflow-x: auto;
        }

        .register-recaptcha-note {
            border: 1px solid #FED7AA;
            border-radius: 12px;
            background: #FFF7ED;
            padding: 14px 16px;
            font-size: 13px;
            color: #9A3412;
        }

        @media (min-width: 520px) {
            .register-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    <form method="POST" action="<?php echo e(route('register.store')); ?>" class="register-form" data-async-auth="true" data-loading-label="Mendaftar...">
        <?php echo csrf_field(); ?>

        <div class="register-field">
            <label for="name" class="register-label">Nama Lengkap</label>
            <div class="register-input-wrap">
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo e(old('name')); ?>"
                    class="input-lib register-input"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    required
                >
                <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <p data-error-for="name" class="hidden text-xs text-red-600 mt-1.5"></p>
            <?php $__errorArgs = ['name'];
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

        <div class="register-grid">
            <div class="register-field">
                <label for="email" class="register-label">Email</label>
                <div class="register-input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        class="input-lib register-input"
                        placeholder="contoh@gmail.com"
                        autocomplete="email"
                        required
                    >
                    <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <p data-error-for="email" class="hidden text-xs text-red-600 mt-1.5"></p>
                <?php $__errorArgs = ['email'];
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

            <div class="register-field">
                <label for="phone" class="register-label">No HP</label>
                <div class="register-input-wrap">
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?php echo e(old('phone')); ?>"
                        class="input-lib register-input"
                        placeholder="08xxxxxxxxxx"
                        autocomplete="tel"
                    >
                    <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <p data-error-for="phone" class="hidden text-xs text-red-600 mt-1.5"></p>
                <?php $__errorArgs = ['phone'];
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
        </div>

        <div class="register-grid">
            <div class="register-field">
                <label for="kelas" class="register-label">Kelas</label>
                <div class="register-input-wrap">
                    <input
                        type="text"
                        id="kelas"
                        name="kelas"
                        value="<?php echo e(old('kelas')); ?>"
                        class="input-lib register-input"
                        placeholder="Contoh: 7"
                    >
                    <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                </div>
                <p data-error-for="kelas" class="hidden text-xs text-red-600 mt-1.5"></p>
                <?php $__errorArgs = ['kelas'];
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

            <div class="register-field">
                <label for="jurusan" class="register-label">Jurusan</label>
                <div class="register-input-wrap">
                    <input
                        type="text"
                        id="jurusan"
                        name="jurusan"
                        value="<?php echo e(old('jurusan')); ?>"
                        class="input-lib register-input"
                        placeholder="Contoh: RPL"
                    >
                    <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <p data-error-for="jurusan" class="hidden text-xs text-red-600 mt-1.5"></p>
                <?php $__errorArgs = ['jurusan'];
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
        </div>

        <div class="register-field">
            <label for="password" class="register-label">Kata Sandi</label>
            <div class="register-input-wrap">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input-lib register-input with-toggle"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    required
                >
                <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password daftar">
                    <svg class="toggle-show w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        <circle cx="12" cy="12" r="3" stroke-width="1.8"></circle>
                    </svg>
                    <svg class="toggle-hide hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.88 5.09A9.77 9.77 0 0112 5c4.48 0 8.27 2.94 9.54 7a9.96 9.96 0 01-4.04 5.14M6.23 6.23A9.97 9.97 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.13-.38 4.46-1.05"></path>
                    </svg>
                </button>
            </div>
            <p data-error-for="password" class="hidden text-xs text-red-600 mt-1.5"></p>
            <?php $__errorArgs = ['password'];
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

        <div class="register-field">
            <label for="password_confirmation" class="register-label">Konfirmasi Kata Sandi</label>
            <div class="register-input-wrap">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="input-lib register-input with-toggle"
                    placeholder="Ulangi kata sandi"
                    autocomplete="new-password"
                    required
                >
                <svg class="register-input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password daftar">
                    <svg class="toggle-show w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        <circle cx="12" cy="12" r="3" stroke-width="1.8"></circle>
                    </svg>
                    <svg class="toggle-hide hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.88 5.09A9.77 9.77 0 0112 5c4.48 0 8.27 2.94 9.54 7a9.96 9.96 0 01-4.04 5.14M6.23 6.23A9.97 9.97 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.13-.38 4.46-1.05"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="space-y-2">
            <?php if($googleRecaptchaEnabled): ?>
                <div class="register-recaptcha">
                    <div class="g-recaptcha" data-sitekey="<?php echo e($googleRecaptchaSiteKey); ?>"></div>
                </div>
            <?php else: ?>
                <div class="register-recaptcha-note">
                    Google reCAPTCHA belum aktif karena key belum diisi di file environment.
                </div>
            <?php endif; ?>

            <p data-error-for="g-recaptcha-response" class="hidden text-xs text-red-600 mt-1.5"></p>
            <?php $__errorArgs = ['g-recaptcha-response'];
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

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-bold text-sm">
            Daftar Sekarang
        </button>
    </form>

    <p class="text-center mt-7 text-sm text-slate-500">
        Sudah punya akun?
        <a href="<?php echo e(route('login')); ?>" class="auth-link">Masuk sekarang</a>
    </p>

    <?php if($googleRecaptchaEnabled): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.password-toggle').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.dataset.target);
                const isPassword = input.type === 'password';

                input.type = isPassword ? 'text' : 'password';
                toggle.querySelector('.toggle-show').classList.toggle('hidden', isPassword);
                toggle.querySelector('.toggle-hide').classList.toggle('hidden', !isPassword);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/auth/register.blade.php ENDPATH**/ ?>