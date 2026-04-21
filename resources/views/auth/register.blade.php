@extends('layouts.auth')

@section('heading', 'Buat Akun')
@section('subheading', 'Daftarkan akun baru untuk masuk ke perpustakaan')

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

    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" data-async-auth="true" data-loading-label="Mendaftar...">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Nama Lengkap</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="Masukkan nama lengkap"
                autocomplete="name"
                required
            >
            <p data-error-for="name" class="hidden text-xs text-red-600 mt-1.5"></p>
            @error('name')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="email" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="contoh@gmail.com"
                    autocomplete="email"
                    required
                >
                <p data-error-for="email" class="hidden text-xs text-red-600 mt-1.5"></p>
                @error('email')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">No HP</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="08xxxxxxxxxx"
                    autocomplete="tel"
                >
                <p data-error-for="phone" class="hidden text-xs text-red-600 mt-1.5"></p>
                @error('phone')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="kelas" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Kelas</label>
                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    value="{{ old('kelas') }}"
                    class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="Contoh: 7"
                >
                <p data-error-for="kelas" class="hidden text-xs text-red-600 mt-1.5"></p>
                @error('kelas')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jurusan" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Jurusan</label>
                <input
                    type="text"
                    id="jurusan"
                    name="jurusan"
                    value="{{ old('jurusan') }}"
                    class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="Contoh: rpl"
                >
                <p data-error-for="jurusan" class="hidden text-xs text-red-600 mt-1.5"></p>
                @error('jurusan')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Kata Sandi</label>
            <div class="relative">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input-lib w-full pl-4 pr-12 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="password"
                    autocomplete="new-password"
                    required
                >
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
            @error('password')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="input-lib w-full pl-4 pr-12 py-3.5 rounded-xl text-sm text-lib-950"
                    placeholder="Ulangi kata sandi"
                    autocomplete="new-password"
                    required
                >
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
            @if ($googleRecaptchaEnabled)
                <div class="overflow-x-auto">
                    <div class="g-recaptcha" data-sitekey="{{ $googleRecaptchaSiteKey }}"></div>
                </div>
            @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Google reCAPTCHA belum aktif karena key belum diisi di file environment.
                </div>
            @endif

            <p data-error-for="g-recaptcha-response" class="hidden text-xs text-red-600 mt-1.5"></p>
            @error('g-recaptcha-response')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Daftar
        </button>
    </form>

    <p class="text-center mt-7 text-sm text-lib-700/60">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="auth-link">Masuk sekarang</a>
    </p>

    @if ($googleRecaptchaEnabled)
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

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
