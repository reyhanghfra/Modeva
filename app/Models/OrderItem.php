<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'variant',
        'quantity',
        'price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * Relasi ke transaksi/pesanan induk.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Format harga satuan dalam mata uang Rupiah.
     */
    public function getFormattedPriceAttribute(): string
    {
        return Rupiah::format($this->price);
    }

    /**
     * Format subtotal item dalam mata uang Rupiah.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return Rupiah::format($this->subtotal);
    }
}
