<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seeder_creates_at_least_six_products_with_variants_for_each_category(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertGreaterThanOrEqual(6, Product::query()->count());
        $this->assertGreaterThan(0, ProductVariant::query()->count());
        $this->assertSame(
            0,
            Product::query()->whereDoesntHave('variants')->count()
        );

        foreach (['Pakaian', 'Sepatu', 'Tas'] as $categoryName) {
            $this->assertGreaterThan(
                0,
                Product::query()
                    ->whereHas('category', fn ($query) => $query->where('name', $categoryName))
                    ->count()
            );
        }
    }
}
