@extends('layouts.auth')

@section('heading')
    <span class="font-serif">Verifikasi Kode</span>
@endsection

@section('subheading')
    Kode verifikasi telah dikirim ke <br>
    <span class="font-semibold text-lib-900 mt-1 inline-block">{{ session('reset_email') }}</span>
@endsection

@section('content')
    <style>
        :root {
            --accent: {{ \App\Models\Setting::valueOr('app_color', '#c4956a') }};
            --accent-glow: {{ \App\Models\Setting::valueOr('app_color', '#c4956a') }}33;
        }

        .code-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 2rem 0;
        }

        .code-input-single {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #E8D5A8;
            border-radius: 12px;
            background: #FEFCF9;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: #2C1810;
        }

        .code-input-single:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--accent-glow);
            outline: none;
            transform: translateY(-2px);
            background: #fff;
        }

        .code-input-single.filled {
            border-color: var(--accent);
            background: #fff;
        }

        .status-alert {
            animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(8px);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .action-link {
            transition: all 0.2s ease;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #5C4320;
            font-weight: 600;
        }

        .action-link:hover {
            background: var(--accent-glow);
            color: var(--accent);
            transform: translateY(-1px);
        }

        .auth-button-premium {
            background: var(--accent);
            color: white;
            width: 100%;
            padding: 1rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px var(--accent-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .auth-button-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px var(--accent-glow);
            filter: brightness(1.05);
        }

        .auth-button-premium:active {
            transform: translateY(0);
        }

        /* Hidden real input */
        #token {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
    </style>

    @if (session('status'))
        <div class="status-alert mb-8 p-4 rounded-2xl bg-green-50/80 border border-green-100 text-green-700 text-xs flex items-center gap-3">
            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.verify_code.post') }}" id="verifyForm">
        @csrf
        
        <div class="relative">
            <label class="block text-[11px] font-bold text-lib-800 uppercase tracking-[0.2em] mb-6 text-center">Masukkan Kode Verifikasi</label>
            
            <input type="text" id="token" name="token" maxlength="6" autocomplete="off">
            
            <div class="code-input-group" id="codeInputGroup">
                <input type="text" class="code-input-single" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]*">
                <input type="text" class="code-input-single" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]*">
                <input type="text" class="code-input-single" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]*">
                <input type="text" class="code-input-single" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]*">
                <input type="text" class="code-input-single" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]*">
                <input type="text" class="code-input-single" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]*">
            </div>

            <div id="error-message" class="hidden text-[11px] text-red-500 mt-4 text-center font-semibold bg-red-50 py-2 rounded-lg border border-red-100 animate-pulse"></div>

            @error('token')
                <p class="text-[11px] text-red-500 mt-4 text-center font-semibold bg-red-50 py-2 rounded-lg border border-red-100 animate-pulse">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" id="submitBtn" class="auth-button-premium mt-8">
            <span class="btn-text">Lanjutkan</span>
            <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
            <svg class="w-4 h-4 hidden animate-spin loading-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </form>

    <div class="mt-12 pt-8 border-t border-lib-100/50">
        <p class="text-[11px] text-lib-700/50 mb-5 text-center font-medium uppercase tracking-wider">Bermasalah dengan kode?</p>
        <div class="space-y-3">
            <form id="resendForm" action="{{ route('password.email') }}" method="POST" class="block w-full">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <button type="submit" id="resendBtn" class="action-link text-xs w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Kirim Ulang Kode</span>
                </button>
            </form>
            <a href="{{ route('password.request') }}" class="action-link text-xs w-full justify-center opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                <span>Ganti Email</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const realInput = document.getElementById('token');
            const visualInputs = document.querySelectorAll('.code-input-single');
            const group = document.getElementById('codeInputGroup');
            const form = document.getElementById('verifyForm');
            const errorDiv = document.getElementById('error-message');
            const submitBtn = document.getElementById('submitBtn');
            const resendForm = document.getElementById('resendForm');

            // Handle typing in visual inputs
            visualInputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    // Only allow numbers
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    
                    const value = e.target.value;
                    if (value && index < visualInputs.length - 1) {
                        visualInputs[index + 1].focus();
                    }
                    updateRealInput();

                    // Auto submit when all 6 filled
                    if (Array.from(visualInputs).every(input => input.value.length === 1)) {
                        handleFormSubmit();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        visualInputs[index - 1].focus();
                    }
                });
            });

            function updateRealInput() {
                let code = '';
                visualInputs.forEach(input => {
                    code += input.value;
                    if (input.value) {
                        input.classList.add('filled');
                    } else {
                        input.classList.remove('filled');
                    }
                });
                realInput.value = code;
                errorDiv.classList.add('hidden');
            }

            // Paste handling
            group.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                pasteData.split('').forEach((char, i) => {
                    if (visualInputs[i]) {
                        visualInputs[i].value = char;
                        visualInputs[i].classList.add('filled');
                    }
                });
                updateRealInput();
                if (pasteData.length === 6) {
                    handleFormSubmit();
                } else if (pasteData.length > 0) {
                    visualInputs[Math.min(pasteData.length, 5)].focus();
                }
            });

            async function handleFormSubmit() {
                const formData = new FormData(form);
                
                // UI Loading state
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-text').textContent = 'Memverifikasi...';
                submitBtn.querySelector('.btn-icon').classList.add('hidden');
                submitBtn.querySelector('.loading-icon').classList.remove('hidden');
                errorDiv.classList.add('hidden');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
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
                        // Error
                        errorDiv.textContent = result.message || 'Terjadi kesalahan.';
                        errorDiv.classList.remove('hidden');
                        
                        // Shake effect
                        group.classList.add('animate-bounce');
                        setTimeout(() => group.classList.remove('animate-bounce'), 500);
                        
                        // Reset inputs
                        visualInputs.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                        realInput.value = '';
                        visualInputs[0].focus();
                    }
                } catch (error) {
                    errorDiv.textContent = 'Gagal menghubungi server.';
                    errorDiv.classList.remove('hidden');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.btn-text').textContent = 'Lanjutkan';
                    submitBtn.querySelector('.btn-icon').classList.remove('hidden');
                    submitBtn.querySelector('.loading-icon').classList.add('hidden');
                }
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                handleFormSubmit();
            });

            // Resend AJAX
            resendForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('resendBtn');
                const originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Mengirim...</span>';

                try {
                    const response = await fetch(resendForm.action, {
                        method: 'POST',
                        body: new FormData(resendForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const result = await response.json();
                    if (response.ok) {
                        alert(result.message);
                    } else {
                        alert(result.message || 'Gagal mengirim ulang.');
                    }
                } catch (error) {
                    alert('Gagal menghubungi server.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            });
        });
    </script>
@endsection

