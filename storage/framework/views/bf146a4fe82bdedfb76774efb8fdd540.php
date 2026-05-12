<!DOCTYPE html>
<html lang="id">
<head>
    <?php
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        $appNameColor = \App\Models\Setting::valueOr('app_name_color', '#21323a');
        $appLogo = \App\Models\Setting::appLogoPath();
        $showAppName = \App\Models\Setting::valueOr('show_app_name', '1') === '1';
        $isLoginPage = request()->routeIs('login');
        $isRegisterPage = request()->routeIs('register');
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Auth'); ?> - <?php echo e($appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                    },
                    colors: {
                        lib: {
                            50: '#FBF8F1',
                            100: '#F5EDD6',
                            200: '#E8D5A8',
                            300: '#D4B978',
                            400: '#C9A84C',
                            500: '#B8923A',
                            600: '#9A7530',
                            700: '#7A5A28',
                            800: '#5C4320',
                            900: '#3D2D15',
                            950: '#2C1810',
                        },
                        forest: {
                            50: '#F0F7F4',
                            100: '#D9EDE2',
                            200: '#B5DBC6',
                            300: '#83C2A0',
                            400: '#52A479',
                            500: '#35875F',
                            600: '#276C4B',
                            700: '#1F573D',
                            800: '#1B4532',
                            900: '#17392A',
                            950: '#0D201A',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: <?php echo e(($isLoginPage || $isRegisterPage) ? "'Plus Jakarta Sans', sans-serif" : "'Inter', sans-serif"); ?>;
            min-height: 100vh;
            overflow-x: hidden;
            color: #2C1810;
        }
        .book-pattern {
            background-color: #1B4532;
            background-image:
                linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.04) 48px, rgba(201,168,76,0.15) 48px, rgba(201,168,76,0.15) 50px, transparent 50px),
                linear-gradient(90deg,
                    rgba(139,90,43,0.25) 0px, rgba(139,90,43,0.25) 18px,
                    rgba(27,69,50,0.3) 18px, rgba(27,69,50,0.3) 20px,
                    rgba(160,82,45,0.2) 20px, rgba(160,82,45,0.2) 34px,
                    rgba(27,69,50,0.3) 34px, rgba(27,69,50,0.3) 36px,
                    rgba(85,107,47,0.2) 36px, rgba(85,107,47,0.2) 52px,
                    rgba(27,69,50,0.3) 52px, rgba(27,69,50,0.3) 54px,
                    rgba(178,34,34,0.15) 54px, rgba(178,34,34,0.15) 70px,
                    rgba(27,69,50,0.3) 70px, rgba(27,69,50,0.3) 72px,
                    rgba(70,100,50,0.2) 72px, rgba(70,100,50,0.2) 88px,
                    rgba(27,69,50,0.3) 88px, rgba(27,69,50,0.3) 90px,
                    rgba(120,60,30,0.2) 90px, rgba(120,60,30,0.2) 108px,
                    rgba(27,69,50,0.3) 108px, rgba(27,69,50,0.3) 110px,
                    rgba(50,80,120,0.15) 110px, rgba(50,80,120,0.15) 125px,
                    rgba(27,69,50,0.3) 125px, rgba(27,69,50,0.3) 127px,
                    transparent 127px
                );
            background-size: 128px 50px;
        }
        .auth-shell {
            background: linear-gradient(160deg, #FDF8F0 0%, #FEFCF9 40%, #FBF8F1 100%);
        }
        .auth-shell.login-shell,
        .auth-shell.register-shell {
            background: #FFFFFF;
            overflow: hidden;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(201,168,76,0.2);
            box-shadow:
                0 25px 60px rgba(0,0,0,0.15),
                0 0 0 1px rgba(255,255,255,0.5) inset,
                0 1px 0 rgba(201,168,76,0.1) inset;
        }
        .brand-glow {
            box-shadow: 0 0 24px rgba(201, 168, 76, 0.22);
        }
        .login-shell .login-card,
        .register-shell .login-card {
            background: #FFFFFF;
            backdrop-filter: none;
            border: 1px solid #E2E8F0;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(148, 163, 184, 0.1);
        }
        .login-shell .brand-glow,
        .register-shell .brand-glow {
            box-shadow: none;
        }
        .logo-slot {
            background: transparent;
            border: none;
            box-shadow: none;
        }
        .logo-slot.has-image {
            width: auto;
            max-width: 240px;
            height: auto;
            max-height: 120px;
            padding: 12px 18px;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        .logo-slot.has-image img {
            padding: 0 !important;
        }
        .input-lib {
            border: 1.5px solid #E8D5A8;
            background: #FEFCF9;
            transition: all 0.25s ease;
        }
        .login-shell .input-lib,
        .register-shell .input-lib {
            border: 2px solid transparent;
            background: #F8FAFC;
            color: #1E293B;
        }
        .input-lib:focus {
            border-color: #C9A84C;
            box-shadow: 0 0 0 3px rgba(201,168,76,0.15), 0 1px 2px rgba(0,0,0,0.05);
            background: #fff;
            outline: none;
        }
        .login-shell .input-lib:focus,
        .register-shell .input-lib:focus {
            border-color: #F97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.08);
            background: #FFFFFF;
        }
        .auth-button {
            background: #C9A15B;
            color: #ffffff;
            transition: all 0.25s ease;
            box-shadow: 0 12px 24px rgba(201,161,91,0.25);
        }
        .login-shell .auth-button,
        .register-shell .auth-button {
            background: #F97316;
            box-shadow: 0 10px 15px rgba(249, 115, 22, 0.2);
        }
        .auth-button:hover {
            background: #bf9550;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(191,149,80,0.3);
        }
        .login-shell .auth-button:hover,
        .register-shell .auth-button:hover {
            background: #EA580C;
            box-shadow: 0 12px 20px rgba(249, 115, 22, 0.25);
        }
        .auth-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .checkbox-lib {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid #D4B978;
            border-radius: 4px;
            background: #FEFCF9;
            position: relative;
        }
        .checkbox-lib:checked {
            background: #C9A15B;
            border-color: #C9A15B;
        }
        .login-shell .checkbox-lib,
        .register-shell .checkbox-lib {
            border: 2px solid #CBD5E1;
            border-radius: 5px;
            background: #FFFFFF;
        }
        .login-shell .checkbox-lib:checked,
        .register-shell .checkbox-lib:checked {
            background: #F97316;
            border-color: #F97316;
        }
        .checkbox-lib:checked::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }
        .auth-link {
            color: #1B4532;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-link:hover { color: #276C4B; }
        .login-shell .auth-link,
        .register-shell .auth-link {
            color: #F97316;
            font-weight: 700;
        }
        .login-shell .auth-link:hover,
        .register-shell .auth-link:hover {
            color: #EA580C;
        }
        .status-box {
            border: 1px solid rgba(39,108,75,0.18);
            background: rgba(240,247,244,0.9);
            color: #1F573D;
        }
        .error-box {
            border: 1px solid rgba(220,38,38,0.15);
            background: rgba(254,242,242,0.9);
            color: #b91c1c;
        }
        .login-shape {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
            pointer-events: none;
        }
        .login-shape-1 {
            width: 600px;
            height: 600px;
            background: #FFF7ED;
            top: -200px;
            left: -100px;
        }
        .login-shape-2 {
            width: 500px;
            height: 500px;
            background: #F1F5F9;
            bottom: -150px;
            right: -100px;
        }
        .login-shape-3 {
            width: 300px;
            height: 300px;
            background: #FFEDD5;
            top: 40%;
            left: 60%;
            transform: translate(-50%, -50%);
        }
        .register-shape {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
            pointer-events: none;
        }
        .register-shape-1 {
            width: 600px;
            height: 600px;
            background: #FFF7ED;
            top: -200px;
            left: -100px;
        }
        .register-shape-2 {
            width: 500px;
            height: 500px;
            background: #F1F5F9;
            bottom: -150px;
            right: -100px;
        }
        @media (max-width: 1023px) {
            .side-illustration { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen">
    <main class="auth-shell <?php echo e($isLoginPage ? 'login-shell' : ($isRegisterPage ? 'register-shell' : '')); ?> min-h-screen flex items-center justify-center px-6 py-12 relative">
        <?php if($isLoginPage): ?>
            <div class="login-shape login-shape-1"></div>
            <div class="login-shape login-shape-2"></div>
            <div class="login-shape login-shape-3"></div>
        <?php elseif($isRegisterPage): ?>
            <div class="register-shape register-shape-1"></div>
            <div class="register-shape register-shape-2"></div>
        <?php else: ?>
            <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-30" style="background: radial-gradient(circle, rgba(201,168,76,0.15), transparent 70%);"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full opacity-20" style="background: radial-gradient(circle, rgba(27,69,50,0.1), transparent 70%);"></div>
        <?php endif; ?>

        <div class="w-full max-w-md relative z-10">
            <div class="lg:hidden flex items-center justify-center gap-3 mb-10">
                <div class="<?php echo e($appLogo && file_exists(public_path($appLogo)) ? 'w-auto max-w-[12rem] h-auto max-h-[4.5rem] px-3' : 'w-11 h-11'); ?> rounded-xl flex items-center justify-center text-lib-300 text-sm font-bold overflow-hidden">
                    <?php if($appLogo && file_exists(public_path($appLogo))): ?>
                        <img src="<?php echo e(asset($appLogo)); ?>" alt="<?php echo e($appName); ?>" class="w-full h-full object-contain">
                    <?php else: ?>
                        <?php echo e(strtoupper(substr($appName, 0, 2))); ?>

                    <?php endif; ?>
                </div>
                <?php if($showAppName): ?>
                    <div class="flex flex-col items-center gap-1.5">
                        <h1 class="text-xl font-bold tracking-tight" style="color: <?php echo e($appNameColor); ?>"><?php echo e($appName); ?></h1>
                    </div>
                <?php endif; ?>
            </div>

            <div class="login-card rounded-3xl p-8 sm:p-10 brand-glow">
                <?php if(session('status')): ?>
                    <div class="status-box rounded-2xl px-4 py-3 text-sm mb-5">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="error-box rounded-2xl px-4 py-3 text-sm mb-5">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?>

                <div class="text-center mb-8">
                    <div class="logo-slot <?php echo e($appLogo && file_exists(public_path($appLogo)) ? 'has-image' : 'w-24 h-24'); ?> inline-flex items-center justify-center rounded-[1.75rem] mb-4 overflow-hidden">
                        <?php if($appLogo && file_exists(public_path($appLogo))): ?>
                            <img src="<?php echo e(asset($appLogo)); ?>" alt="<?php echo e($appName); ?>" class="w-full h-full object-contain">
                        <?php else: ?>
                            <div class="text-center leading-tight">
                                <div class="text-lib-900 text-xs font-semibold tracking-[0.3em] uppercase">Logo</div>
                                <div class="text-[11px] mt-1" style="color: <?php echo e($appNameColor); ?>"><?php echo e($appName); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h2 class="<?php echo e($isLoginPage || $isRegisterPage ? 'text-2xl font-extrabold text-slate-800 mb-2' : 'text-2xl font-serif font-bold text-lib-950 mb-1'); ?>"><?php echo $__env->yieldContent('heading'); ?></h2>
                    <p class="<?php echo e($isLoginPage || $isRegisterPage ? 'text-sm text-slate-500' : 'text-sm text-lib-700/60 font-light'); ?>"><?php echo $__env->yieldContent('subheading'); ?></p>
                </div>

                <div id="authAsyncMessage" class="hidden rounded-2xl px-4 py-3 text-sm mb-5"></div>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </main>

    <script>
        (function () {
            const asyncMessage = document.getElementById('authAsyncMessage');

            function showAsyncMessage(message, tone = 'success') {
                if (!asyncMessage || !message) {
                    return;
                }

                asyncMessage.textContent = message;
                asyncMessage.className = tone === 'error'
                    ? 'rounded-2xl px-4 py-3 text-sm mb-5 error-box'
                    : 'rounded-2xl px-4 py-3 text-sm mb-5 status-box';
            }

            function clearAsyncMessage() {
                if (!asyncMessage) {
                    return;
                }

                asyncMessage.textContent = '';
                asyncMessage.className = 'hidden rounded-2xl px-4 py-3 text-sm mb-5';
            }

            function clearFormErrors(form) {
                form.querySelectorAll('[data-error-for]').forEach((node) => {
                    node.textContent = '';
                    node.classList.add('hidden');
                });
            }

            function applyFormErrors(form, errors = {}) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const target = form.querySelector(`[data-error-for="${field}"]`);
                    if (!target || !messages?.length) {
                        return;
                    }

                    target.textContent = messages[0];
                    target.classList.remove('hidden');
                });
            }

            function setButtonLoading(button, loadingLabel) {
                if (!button) {
                    return;
                }

                button.disabled = true;
                button.dataset.originalLabel = button.innerHTML;
                button.innerHTML = loadingLabel || 'Memproses...';
            }

            function resetButtonLoading(button) {
                if (!button) {
                    return;
                }

                button.disabled = false;
                if (button.dataset.originalLabel) {
                    button.innerHTML = button.dataset.originalLabel;
                }
            }

            document.addEventListener('submit', async function (event) {
                const form = event.target.closest('form[data-async-auth="true"]');
                if (!form) {
                    return;
                }

                event.preventDefault();
                clearAsyncMessage();
                clearFormErrors(form);

                const submitButton = form.querySelector('button[type="submit"]');
                setButtonLoading(submitButton, form.dataset.loadingLabel);

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });

                    if (response.status === 419) {
                        showAsyncMessage('Sesi form sudah kedaluwarsa. Halaman akan dimuat ulang.', 'error');
                        window.setTimeout(() => window.location.reload(), 800);
                        return;
                    }

                    const result = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        applyFormErrors(form, result.errors || {});
                        showAsyncMessage(result.message || Object.values(result.errors || {}).flat()[0] || 'Terjadi kesalahan.', 'error');
                        return;
                    }

                    showAsyncMessage(result.message || 'Berhasil diproses.', 'success');

                    if (form.dataset.resetOnSuccess === 'true') {
                        form.reset();
                    }

                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } catch (error) {
                    showAsyncMessage('Gagal menghubungi server.', 'error');
                } finally {
                    resetButtonLoading(submitButton);
                }
            });
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\HP\Downloads\laravel\perpustakaan sekolah\perpus\resources\views/layouts/auth.blade.php ENDPATH**/ ?>