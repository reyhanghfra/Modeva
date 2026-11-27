<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\SaveCustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    /**
     * Menampilkan formulir penambahan alamat pengiriman baru.
     */
    public function create(Request $request): View
    {
        $user = $request->user()->load('customer');
        $hasExistingAddresses = $user->addresses()->exists();

        return view('pelanggan.profile.addresses.create', compact('user', 'hasExistingAddresses'));
    }

    /**
     * Menyimpan alamat pengiriman baru milik pelanggan.
     */
    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $hasExisting = $user->addresses()->exists();
            $makePrimary = ! $hasExisting || (bool) ($validated['is_primary'] ?? false);

            if ($makePrimary) {
                $user->addresses()->update(['is_primary' => false]);
            }

            $user->addresses()->create([
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'district' => $validated['district'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'is_primary' => $makePrimary,
            ]);

            $this->syncCustomerSummary($user);
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Alamat pengiriman baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir pengeditan alamat pengiriman milik pelanggan.
     */
    public function edit(Request $request, CustomerAddress $address): View
    {
        $this->ensureOwner($request, $address);

        return view('pelanggan.profile.addresses.edit', compact('address'));
    }

    /**
     * Memperbarui alamat pengiriman yang sudah tersimpan.
     */
    public function update(SaveCustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $address, $validated): void {
            $makePrimary = (bool) ($validated['is_primary'] ?? false);

            // Jika sebelumnya alamat ini utama dan tidak ada alamat lain yang utama, tetap jaga sebagai utama
            if (! $makePrimary && $address->is_primary) {
                $otherPrimaryExists = $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->where('is_primary', true)
                    ->exists();

                if (! $otherPrimaryExists) {
                    $makePrimary = true;
                }
            }

            if ($makePrimary) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_primary' => false]);
            }

            $address->update([
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'district' => $validated['district'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'is_primary' => $makePrimary,
            ]);

            $this->syncCustomerSummary($user);
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Alamat pengiriman berhasil diperbarui.');
    }

    /**
     * Menjadikan alamat tertentu sebagai alamat utama pelanggan.
     */
    public function setPrimary(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $user = $request->user();

        DB::transaction(function () use ($user, $address): void {
            $user->addresses()->update(['is_primary' => false]);
            $address->update(['is_primary' => true]);

            $this->syncCustomerSummary($user);
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Alamat utama berhasil diperbarui.');
    }

    /**
     * Menghapus alamat pengiriman milik pelanggan.
     */
    public function destroy(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->ensureOwner($request, $address);

        $user = $request->user();

        DB::transaction(function () use ($user, $address): void {
            $wasPrimary = $address->is_primary;
            $address->delete();

            if ($wasPrimary) {
                $nextAddress = $user->addresses()->latest('id')->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_primary' => true]);
                }
            }

            $this->syncCustomerSummary($user);
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Alamat pengiriman berhasil dihapus.');
    }

    /**
     * Memastikan alamat yang diakses benar-benar milik pelanggan yang sedang login.
     */
    private function ensureOwner(Request $request, CustomerAddress $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke alamat pengiriman ini.');
        }
    }

    /**
     * Menyinkronkan alamat utama ke tabel customers agar konsisten di seluruh modul.
     */
    private function syncCustomerSummary(User $user): void
    {
        $primary = $user->addresses()
            ->orderByDesc('is_primary')
            ->latest('id')
            ->first();

        $existingCustomer = $user->customer()->first();

        if ($primary) {
            $user->customer()->updateOrCreate(
                [],
                [
                    'phone' => $existingCustomer?->phone ?: $primary->phone,
                    'address' => $primary->formatted_full_address,
                ]
            );
        } elseif ($existingCustomer) {
            $existingCustomer->update(['address' => null]);
        }
    }
}
