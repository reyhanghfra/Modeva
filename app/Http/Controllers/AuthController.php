<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan formulir login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        return view('auth.login');
    }

    /**
     * Menangani autentikasi login pengguna.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // Batasi percobaan login: maksimal 5 kali gagal
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => "Terlalu banyak percobaan, coba lagi {$seconds} detik.",
                ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Verifikasi kredensial terlebih dahulu
        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Email atau password salah.',
                ]);
        }

        // Kredensial benar, selanjutnya periksa apakah akun aktif
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Akun Anda dinonaktifkan, hubungi admin.',
                ]);
        }

        // Reset rate limiter saat login berhasil
        RateLimiter::clear($throttleKey);

        // Regenerate session untuk mencegah session fixation
        $request->session()->regenerate();

        // Redirect: role pelanggan menggunakan intended(route('home')), sedangkan admin/petugas selalu ke homeRoute()
        if ($user->role === 'pelanggan') {
            return redirect()->intended(route('pelanggan.dashboard'));
        }

        // Untuk admin dan petugas, abaikan intended URL dan selalu arahkan ke dasbor mereka
        $request->session()->forget('url.intended');

        return redirect()->route($user->homeRoute());
    }

    /**
     * Mengeluarkan pengguna dari sistem (logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
