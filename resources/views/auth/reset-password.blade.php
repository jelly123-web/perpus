@extends('layouts.auth')

@section('heading')
    <span class="font-serif">Password Baru</span>
@endsection

@section('subheading', 'Silakan buat password baru untuk mengamankan akun Anda')

@section('content')
    <style>
        :root {
            --accent: #c4956a;
            --accent-hover: #b78658;
            --accent-glow: rgba(196, 149, 106, 0.35);
        }

        .auth-button-premium {
            width: 100%;
            min-height: 56px;
            padding: 1rem 1.25rem;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.875rem;
            letter-spacing: 0.08em;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px var(--accent-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #d2a177 0%, #c4956a 100%) !important;
            color: #ffffff !important;
            cursor: pointer;
            visibility: visible !important;
            opacity: 1 !important;
            appearance: none;
            -webkit-appearance: none;
            text-transform: uppercase;
        }

        .auth-button-premium:hover {
            background: linear-gradient(135deg, #d9ab83 0%, var(--accent-hover) 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px var(--accent-glow);
        }

        .auth-button-premium:disabled {
            opacity: 0.75;
            cursor: wait;
            transform: none;
        }

        .password-toggle {
            position: absolute;
            inset-block: 0;
            right: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b8923a;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0 0.25rem;
        }

        .password-toggle:hover {
            color: #9a7530;
        }
    </style>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" id="resetPasswordForm">
        @csrf

        <input type="hidden" name="token" value="{{ session('reset_token') }}">
        <input type="hidden" name="email" value="{{ session('reset_email') }}">

        <div>
            <label for="password" class="block text-[11px] font-bold text-lib-800 uppercase tracking-[0.2em] mb-3 ml-1">Password Baru</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-lib-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input-lib w-full pl-12 pr-12 py-4 rounded-2xl text-sm text-lib-950 placeholder-lib-200"
                    placeholder="Masukkan password baru"
                    autocomplete="new-password"
                    required
                    autofocus
                >
                <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password baru">
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
            <div id="error-password" class="hidden text-[11px] text-red-500 mt-2 ml-1 font-medium"></div>
            @error('password')
                <p class="text-[11px] text-red-500 mt-2 ml-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-[11px] font-bold text-lib-800 uppercase tracking-[0.2em] mb-3 ml-1">Konfirmasi Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-lib-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </span>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="input-lib w-full pl-12 pr-12 py-4 rounded-2xl text-sm text-lib-950 placeholder-lib-200"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                    required
                >
                <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
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

        <button type="submit" id="submitBtn" class="auth-button-premium" style="background: linear-gradient(135deg, #d2a177 0%, #c4956a 100%) !important; color: #ffffff !important; display: flex !important; visibility: visible !important; opacity: 1 !important; border: none !important;">
            <span class="btn-text">SIMPAN PASSWORD</span>
            <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg class="w-4 h-4 hidden animate-spin loading-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </form>

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

        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('submitBtn');
            const errorPassword = document.getElementById('error-password');
            
            btn.disabled = true;
            btn.querySelector('.btn-text').textContent = 'Menyimpan...';
            btn.querySelector('.btn-icon').classList.add('hidden');
            btn.querySelector('.loading-icon').classList.remove('hidden');
            errorPassword.classList.add('hidden');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    // Success - Redirect
                    window.location.href = result.redirect;
                } else {
                    // Handle validation errors or general errors
                    let message = result.message || 'Terjadi kesalahan.';
                    if (result.errors) {
                        message = Object.values(result.errors)[0][0];
                    }
                    errorPassword.textContent = message;
                    errorPassword.classList.remove('hidden');
                }
            } catch (error) {
                errorPassword.textContent = 'Gagal menghubungi server.';
                errorPassword.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.querySelector('.btn-text').textContent = 'SIMPAN PASSWORD';
                btn.querySelector('.btn-icon').classList.remove('hidden');
                btn.querySelector('.loading-icon').classList.add('hidden');
            }
        });
    </script>

    <div class="mt-10 text-center">
        <a href="{{ route('login') }}" class="text-xs font-bold text-lib-500 hover:text-lib-800 transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Login
        </a>
    </div>
@endsection
