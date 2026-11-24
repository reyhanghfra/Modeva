<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Order (Pesanan).
 *
 * CATATAN INTEGRASI:
 * Model ini merupakan placeholder minimal untuk relasi modul Verifikasi Pembayaran.
 * Model asli dimiliki oleh Orang 3 (modul Transaksi/Pesanan);
 * pertahankan versi mereka saat merge.
 *
 * Asumsi kolom: order_number (MDV-...), user_id, total, status
 * (menunggu_pembayaran, verifikasi_pembayaran, diproses, dikirim, selesai).
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
        ];
    }

    /**
     * Relasi ke pemilik pesanan (pelanggan).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke riwayat pembayaran pesanan ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
