@extends('layouts.auth')

@section('heading')
    <span class="font-serif">Lupa Password</span>
@endsection

@section('subheading', 'Masukkan email akun untuk menerima kode verifikasi reset password')

@section('content')
    <style>
        :root {
            --accent: {{ \App\Models\Setting::valueOr('app_color', '#c4956a') }};
            --accent-glow: {{ \App\Models\Setting::valueOr('app_color', '#c4956a') }}33;
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
            background: var(--accent);
            color: #fff;
            cursor: pointer;
        }

        .auth-button-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px var(--accent-glow);
            filter: brightness(1.05);
        }

        .auth-button-premium:disabled {
            opacity: 0.75;
            cursor: wait;
            transform: none;
        }

        .step-card {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
            padding: 0.95rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(201, 168, 76, 0.18);
            background: rgba(251, 248, 241, 0.85);
        }

        .step-badge {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-size: 0.9rem;
            font-weight: 700;
            color: #7a5a28;
            background: #fff;
            border: 1px solid rgba(201, 168, 76, 0.35);
            flex-shrink: 0;
        }

        .message-box {
            display: none;
            margin-bottom: 1rem;
            border-radius: 16px;
            padding: 0.9rem 1rem;
            font-size: 12px;
            font-weight: 600;
        }

        .message-box.is-visible {
            display: block;
        }

        .message-box.is-error {
            border: 1px solid rgba(220, 38, 38, 0.15);
            background: rgba(254, 242, 242, 0.95);
            color: #b91c1c;
        }
    </style>

    <div class="step-card">
        <div class="step-badge">1</div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-lib-800">Langkah 1</p>
            <p class="text-xs text-lib-700 mt-1">Masukkan email, lalu klik tombol lanjut untuk pindah ke halaman kode verifikasi.</p>
        </div>
    </div>

    <div id="messageBox" class="message-box"></div>

    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" class="space-y-6">
        @csrf

        <div class="w-full">
            <label for="email" class="block text-[11px] font-bold text-lib-800 uppercase tracking-[0.2em] mb-3 ml-1">Alamat Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-lib-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path>
                    </svg>
                </span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="input-lib w-full pl-12 pr-4 py-4 rounded-2xl text-sm text-lib-950 placeholder-lib-200"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                    autofocus
                >
            </div>
            <div id="error-email" class="hidden text-[11px] text-red-500 mt-2 ml-1 font-medium"></div>
        </div>

        <div class="pt-2">
            <button type="submit" id="submitBtn" class="auth-button-premium">
                <span class="btn-text">LANJUT</span>
                <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
                <svg class="w-4 h-4 hidden animate-spin loading-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
            <p class="mt-3 text-center text-[11px] text-lib-700/70">
                Setelah email valid, halaman akan pindah ke input kode verifikasi.
            </p>
        </div>
    </form>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('submitBtn');
            const errorEmail = document.getElementById('error-email');
            const messageBox = document.getElementById('messageBox');

            errorEmail.classList.add('hidden');
            errorEmail.textContent = '';
            messageBox.className = 'message-box';
            messageBox.textContent = '';

            submitBtn.disabled = true;
            submitBtn.querySelector('.btn-text').textContent = 'MENGIRIM...';
            submitBtn.querySelector('.btn-icon').classList.add('hidden');
            submitBtn.querySelector('.loading-icon').classList.remove('hidden');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name=\"_token\"]').value,
                    },
                });

                const result = await response.json();

                if (response.ok && result.status === 'success' && result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }

                const message = result.errors?.email?.[0] || result.message || 'Terjadi kesalahan.';
                errorEmail.textContent = message;
                errorEmail.classList.remove('hidden');
                messageBox.textContent = message;
                messageBox.className = 'message-box is-visible is-error';
            } catch (error) {
                errorEmail.textContent = 'Gagal menghubungi server.';
                errorEmail.classList.remove('hidden');
                messageBox.textContent = 'Gagal menghubungi server.';
                messageBox.className = 'message-box is-visible is-error';
            } finally {
                submitBtn.disabled = false;
                submitBtn.querySelector('.btn-text').textContent = 'LANJUT';
                submitBtn.querySelector('.btn-icon').classList.remove('hidden');
                submitBtn.querySelector('.loading-icon').classList.add('hidden');
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
