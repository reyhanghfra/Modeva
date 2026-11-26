<?php

namespace Tests\Feature\Models;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public static function stockStatuses(): array
    {
        return [
            'sold out' => [0, 5, 'Habis'],
            'at threshold' => [5, 5, 'Menipis'],
            'below threshold' => [2, 5, 'Menipis'],
            'above threshold' => [6, 5, 'Aman'],
        ];
    }

    #[DataProvider('stockStatuses')]
    public function test_stock_status_matches_quantity_and_threshold(int $stock, int $threshold, string $expectedStatus): void
    {
        $variant = new ProductVariant([
            'stock' => $stock,
            'low_stock_threshold' => $threshold,
        ]);

        $this->assertSame($expectedStatus, $variant->stockStatus());
    }

    public function test_low_stock_scope_excludes_sold_out_and_safe_variants(): void
    {
        $product = Product::factory()->create();
        $lowStockVariant = $product->variants()->create([
            'color' => 'Taupe',
            'size' => 'S',
            'stock' => 3,
            'low_stock_threshold' => 5,
        ]);
        $product->variants()->create([
            'color' => 'Krem',
            'size' => 'S',
            'stock' => 0,
            'low_stock_threshold' => 5,
        ]);
        $product->variants()->create([
            'color' => 'Hitam',
            'size' => 'S',
            'stock' => 6,
            'low_stock_threshold' => 5,
        ]);

        $this->assertSame(
            [$lowStockVariant->id],
            ProductVariant::query()->lowStock()->pluck('id')->all()
        );
    }

    public function test_deleting_a_product_cascades_to_its_variants(): void
    {
        $product = Product::factory()->create();
        $product->variants()->createMany([
            ['color' => 'Taupe', 'size' => 'S', 'stock' => 2, 'low_stock_threshold' => 5],
            ['color' => 'Krem', 'size' => 'M', 'stock' => 4, 'low_stock_threshold' => 5],
        ]);
        $variantIds = $product->variants()->pluck('id');

        $product->delete();

        foreach ($variantIds as $variantId) {
            $this->assertDatabaseMissing('product_variants', ['id' => $variantId]);
        }
    }
}
