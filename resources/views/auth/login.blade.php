@extends('layouts.auth')

@section('heading', 'Welcome')
@section('subheading', 'silahkan login dulu!')

@section('content')
    <style>
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

    <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5" data-async-auth="true" data-loading-label="Masuk...">
        @csrf

        <div>
            <label for="username" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">username</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="nama"
                autocomplete="username"
                required
            >
            <p data-error-for="username" class="hidden text-xs text-red-600 mt-1.5"></p>
            @error('username')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">password</label>
            <div class="relative">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input-lib w-full pl-4 pr-12 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
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

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" class="checkbox-lib" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span class="text-xs text-lib-700/70">Ingat saya</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-xs text-lib-500 hover:text-lib-700 transition-colors">Forgot password?</a>
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Masuk
        </button>
    </form>

    <p class="text-center mt-7 text-sm text-lib-700/60">
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
