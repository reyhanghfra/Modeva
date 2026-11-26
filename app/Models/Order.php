<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Order (Transaksi / Pesanan).
 */
class Order extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_VERIFIKASI_PEMBAYARAN = 'verifikasi_pembayaran';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'order_number',
        'user_id',
        'shipping_address',
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
     * Relasi ke daftar item produk pada pesanan ini.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi ke riwayat pembayaran pesanan ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relasi ke pembayaran terbaru milik pesanan ini.
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Format total transaksi dalam mata uang Rupiah.
     */
    public function getFormattedTotalAttribute(): string
    {
        return Rupiah::format($this->total);
    }

    /**
     * Label status pesanan berbahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
            self::STATUS_VERIFIKASI_PEMBAYARAN => 'Verifikasi Pembayaran',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DIKIRIM => 'Dikirim',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Varian badge warna untuk status pesanan.
     */
    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SELESAI => 'success',
            self::STATUS_DIBATALKAN => 'danger',
            self::STATUS_MENUNGGU_PEMBAYARAN => 'secondary',
            default => 'warning',
        };
    }
}
