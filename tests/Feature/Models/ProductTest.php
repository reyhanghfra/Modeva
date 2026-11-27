<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_stock_attribute_sums_variant_stock_when_not_preloaded(): void
    {
        $product = Product::factory()->create();
        $product->variants()->createMany([
            ['color' => 'Taupe', 'size' => 'S', 'stock' => 4, 'low_stock_threshold' => 5],
            ['color' => 'Krem', 'size' => 'S', 'stock' => 7, 'low_stock_threshold' => 5],
        ]);
        $product->refresh();

        $this->assertSame(11, $product->total_stock);
    }

    public function test_total_stock_attribute_sums_variants_from_preloaded_aggregate(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $product->variants()->createMany([
            ['color' => 'Taupe', 'size' => 'S', 'stock' => 4, 'low_stock_threshold' => 5],
            ['color' => 'Krem', 'size' => 'S', 'stock' => 7, 'low_stock_threshold' => 5],
        ]);

        $productWithTotalStock = Product::query()
            ->withTotalStock()
            ->findOrFail($product->id);

        $this->assertSame(11, $productWithTotalStock->total_stock);
    }

    public function test_product_slug_is_generated_and_made_unique_from_its_name(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create([
            'name' => 'Blazer Kota',
            'slug' => null,
        ]);

        $product = Product::factory()->for($category)->create([
            'name' => 'Blazer: Kota',
            'slug' => null,
        ]);

        $this->assertSame('blazer-kota-1', $product->slug);
    }

    public function test_category_relationship_returns_its_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->assertSame($product->id, $category->products()->firstOrFail()->id);
        $this->assertSame($category->id, $product->category->id);
    }
}
