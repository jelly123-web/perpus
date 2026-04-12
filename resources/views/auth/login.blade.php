@extends('layouts.auth')

@section('heading', 'Welcome')
@section('subheading', 'silahkan login dulu!')

@section('content')
    <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5">
        @csrf

        <div>
            <label for="username" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">sername</label>
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
            @error('username')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
            >
            @error('password')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" class="checkbox-lib" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span class="text-xs text-lib-700/70">Ingat saya</span>
            </label>
            <span class="text-xs text-lib-500">Akses aman untuk anggota</span>
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Masuk
        </button>
    </form>

    <p class="text-center mt-7 text-sm text-lib-700/60">
        Belum punya akun?
        <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
    </p>
@endsection
