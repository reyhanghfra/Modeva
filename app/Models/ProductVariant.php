<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'stock',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    public function stockStatus(): string
    {
        if ($this->stock === 0) {
            return 'Habis';
        }

        if ($this->stock <= $this->low_stock_threshold) {
            return 'Menipis';
        }

        return 'Aman';
    }
}
