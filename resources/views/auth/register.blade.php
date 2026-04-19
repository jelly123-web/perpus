@extends('layouts.auth')

@section('heading', 'Buat Akun')
@section('subheading', 'Daftarkan akun baru untuk masuk ke perpustakaan digital')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
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
                    placeholder="Contoh: XI IPA 2"
                >
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
                    placeholder="Contoh: IPA"
                >
                @error('jurusan')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Kata Sandi</label>
            <input
                type="password"
                id="password"
                name="password"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="password"
                autocomplete="new-password"
                required
            >
            @error('password')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="Ulangi kata sandi"
                autocomplete="new-password"
                required
            >
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
@endsection
