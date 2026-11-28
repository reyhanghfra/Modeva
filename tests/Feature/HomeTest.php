<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_categories_and_products(): void
    {
        $cat = Category::create(['name' => 'Pakaian', 'description' => 'Siluet tenang', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $cat->id,
            'sku' => 'MDV-1',
            'name' => 'Dress Linen',
            'brand' => 'Kala Studio',
            'description' => 'x',
            'price' => 459000,
            'is_active' => true,
        ]);
        $product->variants()->create(['color' => 'Taupe', 'size' => 'M', 'stock' => 5]);

        $this->get('/')->assertOk()
            ->assertSee('Pakaian')
            ->assertSee('Dress Linen')
            ->assertSee('Rp459.000')
            ->assertSee('Sisa 5');
    }
}