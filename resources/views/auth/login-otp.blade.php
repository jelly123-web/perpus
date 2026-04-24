@extends('layouts.auth')

@section('heading', 'Login OTP')
@section('subheading', 'Masukkan email atau username untuk menerima kode OTP')

@section('content')
    @php
        $otpLoginEmail = $otpLoginUser?->email ?? '';
        $otpMaskedEmail = '';

        if ($otpLoginEmail && str_contains($otpLoginEmail, '@')) {
            [$otpName, $otpDomain] = explode('@', $otpLoginEmail, 2);
            $otpVisibleName = substr($otpName, 0, min(strlen($otpName), 2));
            $otpMaskedEmail = $otpVisibleName.str_repeat('*', max(strlen($otpName) - strlen($otpVisibleName), 1)).'@'.$otpDomain;
        }
    @endphp

    <form method="POST" action="{{ route('login.otp.send') }}" class="space-y-4" data-async-auth="true" data-loading-label="Mengirim OTP...">
        @csrf

        <div>
            <label for="identifier" class="block text-xs font-semibold text-lib-800 uppercase tracking-wider mb-2">email / username</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                value="{{ old('identifier', $otpLoginUser?->username ?? '') }}"
                class="input-lib w-full px-4 py-3.5 rounded-xl text-sm text-lib-950"
                placeholder="masukkan email atau username"
                autocomplete="username"
                required
            >
            <p data-error-for="identifier" class="hidden text-xs text-red-600 mt-1.5"></p>
            @error('identifier')
                <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-button w-full py-3.5 rounded-xl font-semibold text-sm tracking-wide">
            Kirim Token OTP
        </button>
    </form>

    @if ($otpLoginUser)
        <div class="mt-5 rounded-2xl border border-forest-200 bg-forest-50 px-4 py-3 text-xs text-forest-700">
            Token OTP sudah dikirim ke {{ $otpMaskedEmail ?: $otpLoginUser->email }}.
        </div>

        <form method="POST" action="{{ route('login.otp.verify') }}" class="space-y-4 mt-5" data-async-auth="true" data-loading-label="Memverifikasi OTP...">
            @csrf

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
                @error('otp')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl border border-lib-300 bg-white px-4 py-3.5 text-sm font-semibold text-lib-900 transition hover:border-lib-400 hover:bg-lib-100">
                Verifikasi OTP dan Masuk
            </button>
        </form>
    @endif

    <p class="text-center mt-7 text-sm text-lib-700/60">
        Kembali ke login password?
        <a href="{{ route('login') }}" class="auth-link">Masuk biasa</a>
    </p>
@endsection
