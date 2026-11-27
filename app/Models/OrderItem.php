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
     * Mendapatkan warna produk dari atribut varian (format "Warna · Ukuran").
     */
    public function getColorAttribute(): string
    {
        [$color] = $this->parseVariantParts();

        return $color;
    }

    /**
     * Mendapatkan ukuran produk dari atribut varian (format "Warna · Ukuran").
     */
    public function getSizeAttribute(): string
    {
        [, $size] = $this->parseVariantParts();

        return $size;
    }

    /**
     * Memecah nilai varian menjadi pasangan [warna, ukuran].
     *
     * @return array{0: string, 1: string}
     */
    protected function parseVariantParts(): array
    {
        $variant = trim((string) ($this->variant ?? ''));

        if ($variant === '') {
            return ['-', '-'];
        }

        $parts = preg_split('/\s*[·|\/,]\s*/u', $variant, 2);

        if (count($parts) === 2) {
            return [
                $parts[0] !== '' ? $parts[0] : '-',
                $parts[1] !== '' ? $parts[1] : '-',
            ];
        }

        if (preg_match('/^(XS|S|M|L|XL|XXL|XXXL|\d{1,2}(\s*Tahun)?)$/i', $variant)) {
            return ['-', $variant];
        }

        return [$variant, '-'];
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
