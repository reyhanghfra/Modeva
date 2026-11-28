<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Menampilkan formulir pendaftaran pelanggan baru.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        return view('auth.register');
    }

    /**
     * Menyimpan data pelanggan baru dan melakukan autentikasi otomatis.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Tetapkan role SELALU 'pelanggan' dan is_active true dari server untuk mencegah mass assignment role
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        // Memicu event Registered Laravel
        event(new Registered($user));

        // Login otomatis sebagai pelanggan baru
        Auth::login($user);

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Redirect ke intended URL (misal checkout/pembayaran yang sebelumnya dibuka) atau ke beranda
        return redirect()->intended(route('pelanggan.dashboard'));
    }
}
