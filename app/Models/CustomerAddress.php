<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'address',
        'district',
        'city',
        'province',
        'postal_code',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Relasi ke akun pelanggan pemilik alamat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format alamat lengkap beserta kecamatan, kota/kabupaten, provinsi, dan kode pos.
     */
    public function getFormattedFullAddressAttribute(): string
    {
        return sprintf(
            '%s, Kec. %s, %s, %s %s',
            trim($this->address),
            trim($this->district),
            trim($this->city),
            trim($this->province),
            trim($this->postal_code)
        );
    }
}
