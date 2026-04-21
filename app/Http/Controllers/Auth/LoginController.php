<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

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
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'min:3'],
            'password' => ['required', 'string', 'min:5'],
        ], [
            'username.required' => 'NIS atau username wajib diisi.',
            'username.min' => 'NIS atau username minimal 3 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 5 karakter.',
        ]);

        $loginValue = $credentials['username'];

        try {
            $user = User::query()->where('username', $loginValue)->first();

            if (! $user) {
                $user = User::query()->where('name', $loginValue)->first();
            }

            if (! $user) {
                $emailMatches = User::query()->where('email', $loginValue)->get();

                if ($emailMatches->count() > 1) {
                    if ($request->ajax()) {
                        return response()->json([
                            'message' => 'Email ini dipakai beberapa akun. Silakan login memakai username/NIS.',
                            'errors' => ['username' => ['Email ini dipakai beberapa akun. Silakan login memakai username/NIS.']],
                        ], 422);
                    }

                    return back()
                        ->withInput($request->only('username', 'remember'))
                        ->withErrors([
                            'username' => 'Email ini dipakai beberapa akun. Silakan login memakai username/NIS.',
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
                    'message' => 'Username/NIS atau kata sandi tidak cocok.',
                    'errors' => ['username' => ['Username/NIS atau kata sandi tidak cocok.']],
                ], 422);
            }

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Username/NIS atau kata sandi tidak cocok.',
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
}
