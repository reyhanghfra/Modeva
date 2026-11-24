<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DIBAYAR = 'belum_dibayar';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_TERVERIFIKASI = 'terverifikasi';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'order_id',
        'method',
        'bank_name',
        'sender_name',
        'amount',
        'proof_path',
        'status',
        'rejection_reason',
        'uploaded_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke pesanan terkait.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Dapatkan nilai relasi dengan penanganan defensif jika tabel relasi belum ada di database.
     */
    public function getRelationValue($key)
    {
        if ($key === 'order' && ! $this->relationLoaded('order')) {
            try {
                if (! Schema::hasTable('orders')) {
                    $this->setRelation('order', null);
                    return null;
                }
            } catch (\Throwable) {
                $this->setRelation('order', null);
                return null;
            }
        }

        return parent::getRelationValue($key);
    }

    /**
     * Relasi ke admin/petugas yang melakukan verifikasi.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Label status berbahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_TERVERIFIKASI => 'Terverifikasi',
            self::STATUS_DITOLAK => 'Ditolak',
            default => 'Belum Dibayar',
        };
    }

    /**
     * Varian warna badge status.
     */
    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'warning',
            self::STATUS_TERVERIFIKASI => 'success',
            self::STATUS_DITOLAK => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Format nominal dalam mata uang Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return Rupiah::format($this->amount);
    }

    /**
     * Memeriksa apakah pembayaran siap diverifikasi.
     */
    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_VERIFIKASI;
    }
}
