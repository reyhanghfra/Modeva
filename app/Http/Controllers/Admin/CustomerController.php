<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar seluruh pelanggan.
     */
    public function index(): View
    {
        $customers = User::customers()
            ->with('customer')
            ->latest('id')
            ->get();

        $totalCustomers = $customers->count();

        return view('admin.customers.index', compact('customers', 'totalCustomers'));
    }

    /**
     * Menampilkan formulir penambahan pelanggan baru.
     */
    public function create(): View
    {
        return view('admin.customers.create');
    }

    /**
     * Menyimpan data pelanggan baru ke basis data.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'pelanggan',
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $user->customer()->create([
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Data pelanggan baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman detail pelanggan.
     */
    public function show(User $customer): View
    {
        $this->ensureCustomerRole($customer);

        $customer->load('customer');

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Menampilkan formulir pengeditan data pelanggan.
     */
    public function edit(User $customer): View
    {
        $this->ensureCustomerRole($customer);

        $customer->load('customer');

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Memperbarui data pelanggan yang ada.
     */
    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        $this->ensureCustomerRole($customer);

        $validated = $request->validated();

        DB::transaction(function () use ($customer, $validated) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? $customer->is_active,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $customer->update($userData);

            $customer->customer()->updateOrCreate(
                [],
                [
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                ]
            );
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Menghapus data pelanggan dari sistem.
     */
    public function destroy(User $customer): RedirectResponse
    {
        $this->ensureCustomerRole($customer);

        if (Schema::hasTable('orders') && $customer->orders()->exists()) {
            return back()->with('error', 'Pelanggan memiliki riwayat pesanan, nonaktifkan akun saja.');
        }

        try {
            $customer->delete();

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Data pelanggan berhasil dihapus.');
        } catch (QueryException $e) {
            return back()->with('error', 'Pelanggan memiliki riwayat data terkait, nonaktifkan akun saja.');
        }
    }

    /**
     * Memastikan user yang diakses melalui modul pelanggan memiliki peran pelanggan.
     */
    private function ensureCustomerRole(User $customer): void
    {
        abort_if($customer->role !== 'pelanggan', 404);
    }
}
