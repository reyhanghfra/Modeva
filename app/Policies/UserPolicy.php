<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar pengguna.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail pengguna.
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat membuat pengguna baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui pengguna.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat menghapus pengguna.
     */
    public function delete(User $user, User $model): bool
    {
        // Hanya admin yang dapat menghapus
        if ($user->role !== 'admin') {
            return false;
        }

        // Tidak boleh menghapus diri sendiri
        if ($user->id === $model->id) {
            return false;
        }

        // Tidak boleh menghapus admin aktif terakhir
        if ($model->role === 'admin' && $model->is_active) {
            $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                return false;
            }
        }

        // Tidak boleh menghapus jika memiliki pesanan
        if (Schema::hasTable('orders')) {
            $hasOrders = \Illuminate\Support\Facades\DB::table('orders')->where('user_id', $model->id)->exists();
            if ($hasOrders) {
                return false;
            }
        }

        return true;
    }
}
