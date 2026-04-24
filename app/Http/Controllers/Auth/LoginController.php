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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
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

    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'min:3'],
            'password' => ['required', 'string', 'min:5'],
        ], [
            'username.required' => 'Username, email, atau NIK/KTP wajib diisi.',
            'username.min' => 'Username, email, atau NIK/KTP minimal 3 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 5 karakter.',
        ]);

        $loginValue = $credentials['username'];

        try {
            $user = User::query()
                ->where('username', $loginValue)
                ->orWhere('nik', $loginValue)
                ->first();

            if (! $user) {
                $emailMatches = User::query()->where('email', $loginValue)->get();

                if ($emailMatches->count() > 1) {
                    if ($request->ajax()) {
                        return response()->json([
                            'message' => 'Email ini dipakai beberapa akun. Silakan login memakai username atau NIK/KTP.',
                            'errors' => ['username' => ['Email ini dipakai beberapa akun. Silakan login memakai username atau NIK/KTP.']],
                        ], 422);
                    }

                    return back()
                        ->withInput($request->only('username', 'remember'))
                        ->withErrors([
                            'username' => 'Email ini dipakai beberapa akun. Silakan login memakai username atau NIK/KTP.',
                        ]);
                }

                $user = $emailMatches->first();
            }
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
                    'message' => 'Username, email, atau NIK/KTP tidak cocok dengan kata sandi.',
                    'errors' => ['username' => ['Username, email, atau NIK/KTP tidak cocok dengan kata sandi.']],
                ], 422);
            }

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Username, email, atau NIK/KTP tidak cocok dengan kata sandi.',
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
            throw new AuthenticationException('Email Google ini terhubung ke beberapa akun. Silakan login memakai username atau NIK/KTP lalu hubungi admin untuk menghubungkan akun Google.');
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
}
