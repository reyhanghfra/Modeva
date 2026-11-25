<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi profil detail pelanggan.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Relasi ke daftar pesanan milik pengguna.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope untuk memfilter pengguna dengan peran pelanggan.
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', 'pelanggan');
    }

    /**
     * Mendapatkan nama route halaman utama berdasarkan role pengguna.
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            default => 'home',
        };
    }
}