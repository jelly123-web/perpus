<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    protected function resolveHomeRouteFor(User $user): string
    {
        $routePriority = [
            'dashboard' => 'access_dashboard',
            'borrower.history' => 'view_borrower_history',
            'admin.loans.index' => 'manage_loans',
            'admin.reports.index' => 'view_reports',
            'admin.users.index' => 'manage_users',
            'admin.roles.index' => 'manage_roles',
            'admin.categories.index' => 'manage_categories',
            'admin.books.index' => 'manage_books',
            'admin.backups.index' => 'manage_backups',
            'admin.settings.index' => 'manage_settings',
        ];

        foreach ($routePriority as $route => $permission) {
            if ($user->hasPermission($permission)) {
                return route($route);
            }
        }

        return route('profile.show');
    }

    public function show(): View
    {
        return view('auth.login', [
            'googleLoginEnabled' => $this->googleLoginEnabled(),
        ]);
    }

    public function showOtpForm(): View
    {
        $otpLoginUser = null;
        $otpLoginUserId = session('login_otp_user_id');

        if ($otpLoginUserId) {
            $otpLoginUser = User::query()->find($otpLoginUserId);

            if (! $otpLoginUser) {
                session()->forget('login_otp_user_id');
            }
        }

        return view('auth.login-otp', [
            'otpLoginUser' => $otpLoginUser,
        ]);
    }

    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'min:3'],
            'password' => ['required', 'string', 'min:5'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 5 karakter.',
        ]);

        $loginValue = $credentials['username'];

        try {
            $user = User::query()
                ->where('username', $loginValue)
                ->first();
        } catch (QueryException $exception) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Koneksi database belum tersedia. Nyalakan MySQL lalu coba lagi.',
                    'errors' => ['username' => ['Koneksi database belum tersedia. Nyalakan MySQL lalu coba lagi.']],
                ], 500);
            }

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Koneksi database belum tersedia. Nyalakan MySQL lalu coba lagi.',
                ]);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Username tidak cocok dengan kata sandi.',
                    'errors' => ['username' => ['Username tidak cocok dengan kata sandi.']],
                ], 422);
            }

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Username tidak cocok dengan kata sandi.',
                ]);
        }

        if (! $user->is_active) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Akun ini sedang dinonaktifkan.',
                    'errors' => ['username' => ['Akun ini sedang dinonaktifkan.']],
                ], 422);
            }

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Akun ini sedang dinonaktifkan.',
                ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLogger::log('auth', 'login', 'Berhasil login ke sistem');

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Selamat datang, '.$user->name.'!',
                'redirect' => $this->resolveHomeRouteFor($user),
            ]);
        }

        return redirect()->intended($this->resolveHomeRouteFor($user))
            ->with('status', 'Selamat datang, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        ActivityLogger::log('auth', 'logout', 'Berhasil logout dari sistem');
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Anda berhasil logout.',
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('status', 'Anda berhasil logout.');
    }

    public function sendOtp(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'min:3'],
        ], [
            'identifier.required' => 'Username atau email wajib diisi.',
            'identifier.min' => 'Username atau email minimal 3 karakter.',
        ]);

        try {
            $user = $this->resolveUserForLoginIdentifier($validated['identifier']);
        } catch (AuthenticationException $exception) {
            return $this->otpErrorResponse($request, $exception->getMessage(), 422);
        }

        if (! $user) {
            return $this->otpErrorResponse($request, 'Username atau email tidak ditemukan.', 422);
        }

        if (! $user->is_active) {
            return $this->otpErrorResponse($request, 'Akun ini sedang dinonaktifkan.', 422);
        }

        if (blank($user->email)) {
            return $this->otpErrorResponse($request, 'Akun ini belum memiliki email untuk menerima OTP. Hubungi admin.', 422);
        }

        if ($mailError = $this->mailConfigurationError()) {
            return $this->otpErrorResponse($request, $mailError, 422);
        }

        try {
            $token = (string) random_int(100000, 999999);

            DB::table('login_otp_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            $user->sendLoginOtpNotification($token);
        } catch (Throwable $exception) {
            report($exception);

            Log::warning('OTP email delivery failed.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'exception' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            return $this->otpErrorResponse($request, $this->mailDeliveryErrorMessage($exception), 500);
        }

        $request->session()->put('login_otp_user_id', $user->id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kode OTP sudah dikirim ke email '.$this->maskEmail($user->email).'.',
                'redirect' => route('login.otp'),
            ]);
        }

        return redirect()->route('login.otp')
            ->with('status', 'Kode OTP sudah dikirim ke email '.$this->maskEmail($user->email).'.');
    }

    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'string'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
        ]);

        $otpLoginUserId = $request->session()->get('login_otp_user_id');

        if (! $otpLoginUserId) {
            return $this->otpErrorResponse($request, 'Sesi OTP sudah habis. Minta kode baru lagi.', 440);
        }

        $user = User::query()->find($otpLoginUserId);

        if (! $user) {
            $request->session()->forget('login_otp_user_id');

            return $this->otpErrorResponse($request, 'Akun untuk login OTP tidak ditemukan.', 422);
        }

        if (! $user->is_active) {
            return $this->otpErrorResponse($request, 'Akun ini sedang dinonaktifkan.', 422);
        }

        $record = DB::table('login_otp_tokens')->where('user_id', $user->id)->first();

        if (! $record || ! Hash::check($validated['otp'], $record->token)
            || Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return $this->otpErrorResponse($request, 'Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
        }

        DB::table('login_otp_tokens')->where('user_id', $user->id)->delete();

        Auth::login($user);
        $request->session()->forget('login_otp_user_id');
        $request->session()->regenerate();

        ActivityLogger::log('auth', 'login', 'Berhasil login ke sistem melalui OTP');

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Selamat datang, '.$user->name.'!',
                'redirect' => $this->resolveHomeRouteFor($user),
            ]);
        }

        return redirect()->intended($this->resolveHomeRouteFor($user))
            ->with('status', 'Selamat datang, '.$user->name.'!');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! $this->googleLoginEnabled()) {
            return redirect()->route('login')
                ->withErrors(['username' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, dan GOOGLE_REDIRECT_URI terlebih dahulu.']);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (! $this->googleLoginEnabled()) {
            return redirect()->route('login')
                ->withErrors(['username' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, dan GOOGLE_REDIRECT_URI terlebih dahulu.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $this->resolveGoogleUser($googleUser);
        } catch (AuthenticationException $exception) {
            report($exception);

            return redirect()->route('login')
                ->withErrors(['username' => $exception->getMessage() ?: 'Otentikasi Google gagal. Silakan coba lagi.']);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('login')
                ->withErrors(['username' => $exception->getMessage() ?: 'Login Google gagal diproses.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        ActivityLogger::log('auth', 'login', 'Berhasil login ke sistem melalui Google');

        return redirect()->intended($this->resolveHomeRouteFor($user))
            ->with('status', 'Selamat datang, '.$user->name.'!');
    }

    private function resolveGoogleUser(object $googleUser): User
    {
        $googleId = (string) ($googleUser->id ?? '');
        $email = trim((string) ($googleUser->email ?? ''));
        $name = trim((string) ($googleUser->name ?? ''));

        if ($googleId === '') {
            throw new AuthenticationException('Google tidak mengirim identitas akun yang valid.');
        }

        if ($email === '') {
            throw new AuthenticationException('Akun Google ini tidak memiliki email yang bisa dipakai untuk login.');
        }

        $existingByGoogleId = User::query()->where('google_id', $googleId)->first();

        if ($existingByGoogleId) {
            if (! $existingByGoogleId->is_active) {
                throw new AuthenticationException('Akun ini sedang dinonaktifkan.');
            }

            if (! $existingByGoogleId->hasVerifiedEmail()) {
                $existingByGoogleId->forceFill([
                    'email_verified_at' => Carbon::now(),
                ])->save();
            }

            return $existingByGoogleId;
        }

        $emailMatches = User::query()->where('email', $email)->get();

        if ($emailMatches->count() > 1) {
            throw new AuthenticationException('Email Google ini terhubung ke beberapa akun. Silakan login memakai username lalu hubungi admin untuk menghubungkan akun Google.');
        }

        $user = $emailMatches->first();

        if ($user) {
            if (! $user->is_active) {
                throw new AuthenticationException('Akun ini sedang dinonaktifkan.');
            }

            $user->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?: Carbon::now(),
            ])->save();

            return $user;
        }

        $user = User::query()->create([
            'name' => $name !== '' ? $name : Str::before($email, '@'),
            'username' => $this->generateUniqueUsername($email, $name),
            'email' => $email,
            'role_id' => $this->resolveGoogleRoleId(),
            'is_active' => true,
            'password' => Str::random(40),
            'google_id' => $googleId,
            'email_verified_at' => Carbon::now(),
        ]);

        return $user;
    }

    private function generateUniqueUsername(string $email, string $name = ''): string
    {
        $baseUsername = Str::of($email)->before('@')->slug('_')->value();

        if ($baseUsername === '') {
            $baseUsername = Str::of($name)->slug('_')->value();
        }

        $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
        $username = $baseUsername;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $baseUsername.'_'.$counter;
            $counter++;
        }

        return $username;
    }

    private function resolveGoogleRoleId(): int
    {
        $roleId = Role::query()->where('name', 'siswa')->value('id');

        if (! $roleId) {
            throw new RuntimeException('Role siswa belum tersedia. Buat role siswa terlebih dahulu sebelum mengaktifkan login Google.');
        }

        return (int) $roleId;
    }

    private function googleLoginEnabled(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    private function resolveUserForLoginIdentifier(string $identifier): ?User
    {
        $user = User::query()
            ->where('username', $identifier)
            ->first();

        if ($user) {
            return $user;
        }

        $emailMatches = User::query()->where('email', $identifier)->get();

        if ($emailMatches->count() > 1) {
            throw new AuthenticationException('Email ini dipakai beberapa akun. Silakan login memakai username.');
        }

        return $emailMatches->first();
    }

    private function otpErrorResponse(Request $request, string $message, int $status): RedirectResponse|JsonResponse
    {
        $field = $request->has('otp') ? 'otp' : 'identifier';

        if ($request->ajax()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], $status);
        }

        return back()
            ->withInput($request->except('otp'))
            ->withErrors([$field => $message]);
    }

    private function mailConfigurationError(): ?string
    {
        $defaultMailer = (string) config('mail.default');

        if (in_array($defaultMailer, ['array', 'log'], true)) {
            return 'Mailer belum diarahkan ke SMTP. Atur MAIL_MAILER=smtp agar OTP bisa dikirim ke email.';
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
                return "Konfigurasi {$key} belum diisi, jadi OTP login belum bisa dikirim.";
            }
        }

        return null;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $email;
        }

        $visibleName = Str::substr($name, 0, min(2, Str::length($name)));
        $hiddenName = str_repeat('*', max(Str::length($name) - Str::length($visibleName), 1));

        return $visibleName.$hiddenName.'@'.$domain;
    }

    private function mailDeliveryErrorMessage(Throwable $exception): string
    {
        $message = Str::lower($exception->getMessage());

        if (str_contains($message, '535-5.7.8') || str_contains($message, 'badcredentials') || str_contains($message, 'username and password not accepted')) {
            return 'Login ke SMTP Gmail ditolak. Cek lagi email pengirim dan App Password Gmail yang dipakai.';
        }

        if (str_contains($message, 'access permissions') || str_contains($message, 'connection could not be established') || str_contains($message, 'timed out')) {
            return 'Koneksi ke server SMTP gagal. Coba pakai Gmail SMTP port 587 dengan TLS, lalu restart server Laravel.';
        }

        if (str_contains($message, 'unsupported scheme')) {
            return 'Skema SMTP tidak valid. Pakai MAIL_ENCRYPTION=tls untuk port 587 atau kosongkan MAIL_SCHEME.';
        }

        return 'OTP gagal dikirim ke email. Periksa konfigurasi SMTP lalu coba lagi.';
    }
}
