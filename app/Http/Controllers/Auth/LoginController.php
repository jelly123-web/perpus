<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
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
                    return back()
                        ->withInput($request->only('username', 'remember'))
                        ->withErrors([
                            'username' => 'Email ini dipakai beberapa akun. Silakan login memakai username/NIS.',
                        ]);
                }

                $user = $emailMatches->first();
            }
        } catch (QueryException $exception) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Koneksi database belum tersedia. Nyalakan MySQL lalu coba lagi.',
                ]);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Username/NIS atau kata sandi tidak cocok.',
                ]);
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Akun ini sedang dinonaktifkan.',
                ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Selamat datang, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda berhasil logout.');
    }
}
