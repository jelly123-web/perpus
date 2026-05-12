@extends('layouts.auth')

@section('heading', 'Welcome')
@section('subheading', 'silahkan login dulu!')

@section('content')
    <style>
        .login-page-form .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .login-page-form .input-wrap {
            position: relative;
        }

        .login-page-form .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .login-page-form .input-lib {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            border-radius: 12px;
        }

        .login-page-form .input-wrap:focus-within .input-icon {
            color: #F97316;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0 0.25rem;
        }

        .password-toggle:hover {
            color: #F97316;
        }

        .auth-icon-link {
            min-height: 48px;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        .social-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width: 480px) {
            .social-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <form method="POST" action="{{ route('login.authenticate') }}" class="login-page-form space-y-5" data-async-auth="true" data-loading-label="Masuk...">
        @csrf

        <div>
            <label for="username" class="form-label">username</label>
            <div class="input-wrap">
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    class="input-lib text-sm text-lib-950"
                    placeholder="masukkan username"
                    autocomplete="username"
                    required
                >
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <p data-error-for="username" class="hidden text-xs text-red-600 mt-1.5"></p>
            @error('username')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input-lib pr-12 text-sm text-lib-950"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password login">
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
            @error('password')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" class="checkbox-lib" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span class="text-xs text-slate-500">Ingat saya</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-orange-500 hover:text-orange-600 transition-colors">Forgot password?</a>
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Masuk
        </button>
    </form>
    
    <div class="my-7 flex items-center gap-4">
        <div class="h-px flex-1 bg-slate-100"></div>
        <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-300">atau</span>
        <div class="h-px flex-1 bg-slate-100"></div>
    </div>

    <div class="social-grid">
        <a
            href="{{ route('login.otp') }}"
            class="auth-icon-link inline-flex items-center justify-center gap-2 border border-slate-200 bg-white text-slate-800 transition hover:border-slate-300 hover:bg-slate-50"
            aria-label="Login pakai OTP"
            title="Login pakai OTP"
        >
            <svg aria-hidden="true" viewBox="0 0 64 64" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="8" y="6" width="28" height="52" rx="6"></rect>
                <path d="M17 12h10"></path>
                <circle cx="30" cy="12" r="1.5" fill="currentColor" stroke="none"></circle>
                <rect x="17" y="49" width="10" height="5" rx="2.5"></rect>
                <path d="M8 17h28"></path>
                <path d="M43 21c7.73 0 14 5.82 14 13 0 4.31-2.26 8.13-5.73 10.5-.62 1.92-.13 4.28 1.73 6-2.91.08-5.23-1-6.77-2.17A15.5 15.5 0 0 1 43 47c-7.73 0-14-5.82-14-13s6.27-13 14-13Z"></path>
                <path d="M36.5 36h13"></path>
                <path d="M36.5 31h4"></path>
                <path d="M42.5 31h3"></path>
                <path d="M47.5 31h2"></path>
            </svg>
            <span>Login OTP</span>
        </a>

        <a
            href="{{ route('auth.google.redirect') }}"
            class="auth-icon-link inline-flex items-center justify-center gap-2 border border-slate-200 bg-white text-slate-800 transition hover:border-slate-300 hover:bg-slate-50"
            aria-label="Masuk dengan Google"
            title="Masuk dengan Google"
        >
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-6 w-6">
                <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.23 1.26-.96 2.33-2.03 3.05l3.28 2.54c1.91-1.76 3.01-4.35 3.01-7.43 0-.72-.06-1.41-.19-2.08H12z"/>
                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.44l-3.28-2.54c-.91.61-2.08.98-3.33.98-2.56 0-4.72-1.73-5.49-4.05H3.12v2.62A9.99 9.99 0 0012 22z"/>
                <path fill="#4A90E2" d="M6.51 13.95A5.99 5.99 0 016.2 12c0-.68.12-1.34.31-1.95V7.43H3.12A9.99 9.99 0 002 12c0 1.61.39 3.13 1.12 4.57l3.39-2.62z"/>
                <path fill="#FBBC05" d="M12 5.98c1.47 0 2.79.51 3.83 1.49l2.87-2.87C16.95 2.98 14.69 2 12 2a9.99 9.99 0 00-8.88 5.43l3.39 2.62C7.28 7.71 9.44 5.98 12 5.98z"/>
            </svg>
            <span>Google</span>
        </a>
    </div>

    <p class="text-center mt-8 text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
    </p>

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
@endsection
