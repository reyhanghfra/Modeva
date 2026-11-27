<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_variant_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Relasi ke akun pelanggan pemilik keranjang.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke varian produk yang dipilih.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Harga satuan produk (integer Rupiah).
     */
    public function getUnitPriceAttribute(): int
    {
        return (int) round((float) ($this->variant?->product?->price ?? 0));
    }

    /**
     * Subtotal item (harga satuan x jumlah).
     */
    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Format harga satuan dalam mata uang Rupiah.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return Rupiah::format($this->unit_price);
    }

    /**
     * Format subtotal item dalam mata uang Rupiah.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return Rupiah::format($this->subtotal);
    }

    /**
     * URL gambar produk jika file tersedia di penyimpanan publik/eksternal.
     */
    public function getProductImageUrlAttribute(): ?string
    {
        $image = $this->variant?->product?->image;

        if (! $image) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        if (Storage::disk('public')->exists($image)) {
            return Storage::disk('public')->url($image);
        }

        if (file_exists(public_path($image))) {
            return asset($image);
        }

        return null;
    }
}
