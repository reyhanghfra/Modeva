<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Memastikan pengguna memiliki role yang sesuai dan akun dalam keadaan aktif.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Jika belum login, redirect ke halaman login
        if (! $user) {
            return redirect()->route('login');
        }

        // Jika akun sedang login tetapi statusnya nonaktif, keluarkan seketika
        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan, hubungi admin.',
            ]);
        }

        // Gabungkan role yang diberikan jika menggunakan pemisah koma (contoh: role:admin,petugas)
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $allowedRoles = array_merge($allowedRoles, explode(',', $role));
            } else {
                $allowedRoles[] = $role;
            }
        }

        // Jika role user tidak memenuhi kriteria, kembalikan 403 Forbidden dengan view 403
        if (! in_array($user->role, $allowedRoles, true)) {
            return response()->view('errors.403', [
                'user' => $user,
            ], 403);
        }

        return $next($request);
    }
}
