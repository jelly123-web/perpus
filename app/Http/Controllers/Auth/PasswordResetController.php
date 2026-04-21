<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        if ($this->emailMatchesMultipleAccounts($validated['email'])) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Email ini dipakai beberapa akun. Hubungi admin.'], 422);
            }
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Email ini dipakai beberapa akun. Hubungi admin atau gunakan username/NIS untuk bantuan reset password.',
                ]);
        }

        if ($mailError = $this->mailConfigurationError()) {
            if ($request->ajax()) {
                return response()->json(['message' => $mailError], 422);
            }
            return back()
                ->withInput()
                ->withErrors([
                    'email' => $mailError,
                ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Email tidak ditemukan.'], 422);
            }
            return back()
                ->withInput()
                ->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        try {
            // Generate 6-digit code
            $token = (string) rand(100000, 999999);

            // Store in password_reset_tokens table (Laravel default table)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            // Send notification
            $user->sendPasswordResetNotification($token);

        } catch (Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Gagal mengirim email. Periksa SMTP.'], 500);
            }
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Email reset password gagal dikirim. Periksa konfigurasi SMTP lalu coba lagi.',
                ]);
        }

        session(['reset_email' => $validated['email']]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('password.verify_code'),
                'message' => 'Kode verifikasi sudah dikirim ke email Anda.',
            ]);
        }

        return redirect()->route('password.verify_code')->with('status', 'Kode verifikasi sudah dikirim ke email Anda.');
    }

    public function showVerifyCodeForm(): View|RedirectResponse
    {
        if (! session('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-code');
    }

    public function verifyCode(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ], [
            'token.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $email = session('reset_email');
        if (! $email) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Sesi reset password sudah habis. Masukkan email lagi.'], 440);
            }
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Akun untuk proses reset tidak ditemukan. Masukkan email lagi.'], 422);
            }
            return redirect()->route('password.request');
        }

        // Verify token manually since we stored it hashed in password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($request->token, $record->token) || 
            Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.'], 422);
            }
            return back()->withErrors(['token' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.']);
        }

        session(['reset_token' => $request->token]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('password.reset'),
            ]);
        }

        return redirect()->route('password.reset');
    }

    public function showResetForm(): View|RedirectResponse
    {
        if (! session('reset_email') || ! session('reset_token')) {
            return redirect()->route('password.verify_code');
        }

        return view('auth.reset-password');
    }

    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($this->emailMatchesMultipleAccounts($validated['email'])) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Email ini dipakai beberapa akun.'], 422);
            }
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'email' => 'Email ini dipakai beberapa akun. Reset password otomatis tidak bisa diproses untuk email ini.',
                ]);
        }

        // Manual reset logic to match our manual token handling
        $user = User::where('email', $validated['email'])->first();
        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        if (!$user || !$record || !Hash::check($validated['token'], $record->token)) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.'], 422);
            }
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['token' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.']);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
        session()->forget(['reset_email', 'reset_token']);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('login'),
                'message' => 'Password Anda telah berhasil diperbarui.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Password Anda telah berhasil diperbarui.');
    }

    private function emailMatchesMultipleAccounts(string $email): bool
    {
        return User::query()
            ->where('email', $email)
            ->count() > 1;
    }

    private function mailConfigurationError(): ?string
    {
        $defaultMailer = (string) config('mail.default');

        if (in_array($defaultMailer, ['array', 'log'], true)) {
            return 'Mailer belum diarahkan ke SMTP. Atur MAIL_MAILER=smtp agar link reset bisa dikirim ke email.';
        }

        if ($defaultMailer !== 'smtp') {
            return null;
        }

        $requiredConfig = [
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
        ];

        foreach ($requiredConfig as $key => $value) {
            if (blank($value)) {
                return "Konfigurasi {$key} belum diisi, jadi email reset password belum bisa dikirim.";
            }
        }

        return null;
    }

    private function messageForStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email tidak ditemukan.',
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            Password::RESET_THROTTLED => 'Permintaan reset terlalu sering. Coba lagi beberapa saat.',
            default => 'Reset password tidak dapat diproses saat ini. Coba lagi.',
        };
    }
}
