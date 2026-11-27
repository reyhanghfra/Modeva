<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman Akun Saya / Profil Saya beserta daftar alamat pengiriman pelanggan.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'customer',
            'addresses' => fn ($query) => $query->orderByDesc('is_primary')->latest('id'),
        ]);

        $customer = $user->customer;
        $addresses = $user->addresses;

        return view('pelanggan.profile.index', compact('user', 'customer', 'addresses'));
    }

    /**
     * Memperbarui informasi profil pelanggan (nama, email, nomor telepon).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $user->customer()->updateOrCreate(
                [],
                [
                    'phone' => $validated['phone'],
                ]
            );
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Informasi profil Anda berhasil diperbarui.');
    }
}
