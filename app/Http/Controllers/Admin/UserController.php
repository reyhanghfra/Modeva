<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna dengan filter dan pencarian.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Pencarian nama atau email
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan peran (role)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan status aktif
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();
        $totalUsers = User::count();

        return view('admin.users.index', compact('users', 'totalUsers'));
    }

    /**
     * Menampilkan formulir penambahan pengguna baru.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan pengguna baru ke basis data.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir pengeditan pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna yang ada.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        $isActive = $request->boolean('is_active');
        $newRole = $request->role;

        // Aturan keamanan 1: Admin tidak boleh menonaktifkan atau menurunkan role dirinya sendiri
        if ($currentUser->id === $user->id) {
            if (! $isActive) {
                return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
            if ($newRole !== 'admin') {
                return back()->withInput()->with('error', 'Anda tidak dapat menurunkan role akun Anda sendiri.');
            }
        }

        // Aturan keamanan 2: Tidak boleh menonaktifkan atau menurunkan role admin aktif terakhir
        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                if (! $isActive) {
                    return back()->withInput()->with('error', 'Tidak dapat menonaktifkan admin aktif terakhir.');
                }
                if ($newRole !== 'admin') {
                    return back()->withInput()->with('error', 'Tidak dapat menurunkan role admin aktif terakhir.');
                }
            }
        }

        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Mengubah status aktif / nonaktif pengguna dengan cepat.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        // Keamanan: Admin tidak boleh menonaktifkan diri sendiri
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        // Keamanan: Tidak boleh menonaktifkan admin aktif terakhir
        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Tidak dapat menonaktifkan admin aktif terakhir.');
            }
        }

        $user->update(['is_active' => ! $user->is_active]);
        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusText}.");
    }

    /**
     * Menghapus pengguna dari sistem.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        // Keamanan: Admin tidak boleh menghapus dirinya sendiri
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Keamanan: Tidak boleh menghapus admin aktif terakhir
        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Tidak dapat menghapus admin aktif terakhir.');
            }
        }

        // Keamanan: Tolak jika pengguna memiliki riwayat pesanan
        if (Schema::hasTable('orders')) {
            $hasOrders = DB::table('orders')->where('user_id', $user->id)->exists();
            if ($hasOrders) {
                return back()->with('error', 'Pengguna memiliki riwayat pesanan, nonaktifkan saja.');
            }
        }

        try {
            $user->delete();
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (QueryException $e) {
            return back()->with('error', 'Pengguna memiliki riwayat data terkait, nonaktifkan saja.');
        }
    }
}
