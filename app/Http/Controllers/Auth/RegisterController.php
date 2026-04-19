<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', [
            'googleRecaptchaEnabled' => $this->googleRecaptchaEnabled(),
            'googleRecaptchaSiteKey' => (string) config('services.recaptcha.site_key'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
            'g-recaptcha-response' => $this->googleRecaptchaEnabled()
                ? ['required', 'string']
                : ['nullable', 'string'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'kelas.max' => 'Kelas maksimal 100 karakter.',
            'jurusan.max' => 'Jurusan maksimal 100 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 5 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'g-recaptcha-response.required' => 'Verifikasi Google reCAPTCHA wajib diselesaikan.',
        ]);

        $recaptchaError = $this->verifyGoogleRecaptcha($request, $validated['g-recaptcha-response'] ?? null);

        if ($recaptchaError !== null) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'g-recaptcha-response' => $recaptchaError,
                ]);
        }

        $user = User::query()->create(collect($validated)->only([
            'name',
            'email',
            'phone',
            'kelas',
            'jurusan',
            'password',
        ])->all());
        $user->update([
            'username' => $this->generateUniqueUsername($user->email),
            'role_id' => Role::query()->where('name', 'siswa')->value('id'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'Akun berhasil dibuat. Kami sudah mengirim link verifikasi ke email Anda.');
    }

    private function generateUniqueUsername(string $email): string
    {
        $baseUsername = Str::of($email)->before('@')->slug('_')->value() ?: 'user';
        $username = $baseUsername;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $baseUsername.'_'.$counter;
            $counter++;
        }

        return $username;
    }

    private function googleRecaptchaEnabled(): bool
    {
        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key'));
    }

    private function verifyGoogleRecaptcha(Request $request, ?string $token): ?string
    {
        if (! $this->googleRecaptchaEnabled()) {
            return 'Google reCAPTCHA belum dikonfigurasi. Lengkapi key reCAPTCHA terlebih dahulu.';
        }

        if (blank($token)) {
            return 'Verifikasi Google reCAPTCHA wajib diselesaikan.';
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (! $response->ok()) {
                return 'Google reCAPTCHA tidak dapat diverifikasi saat ini. Coba lagi.';
            }

            $payload = $response->json();

            if (($payload['success'] ?? false) === true) {
                return null;
            }

            $errorCodes = collect($payload['error-codes'] ?? []);

            if ($errorCodes->contains('timeout-or-duplicate')) {
                return 'Token Google reCAPTCHA sudah kedaluwarsa atau sudah dipakai. Silakan verifikasi ulang.';
            }

            if ($errorCodes->contains('missing-input-response') || $errorCodes->contains('invalid-input-response')) {
                return 'Token Google reCAPTCHA tidak valid. Silakan coba lagi.';
            }

            if ($errorCodes->contains('missing-input-secret') || $errorCodes->contains('invalid-input-secret')) {
                return 'Konfigurasi secret Google reCAPTCHA belum valid.';
            }

            return 'Verifikasi Google reCAPTCHA gagal. Silakan coba lagi.';
        } catch (\Throwable) {
            return 'Koneksi ke Google reCAPTCHA gagal. Coba lagi saat koneksi stabil.';
        }
    }
}
