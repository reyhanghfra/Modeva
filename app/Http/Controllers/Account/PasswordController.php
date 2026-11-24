<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Menampilkan formulir ubah password (halaman Pengaturan).
     */
    public function edit(Request $request): View
    {
        return view('admin.password.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Memperbarui kata sandi akun pengguna.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newPassword = $request->password;

        // Perbarui password di database
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Logout dari perangkat lain demi keamanan
        Auth::logoutOtherDevices($newPassword);

        // Regenerate session id
        $request->session()->regenerate();

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}
