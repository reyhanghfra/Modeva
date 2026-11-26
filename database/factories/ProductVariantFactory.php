<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color' => fake()->randomElement(['Taupe', 'Krem', 'Hitam', 'Arang', 'Camel', 'Putih']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', '37', '38', '39', '40', '41', 'Satu Ukuran']),
            'stock' => fake()->numberBetween(0, 40),
            'low_stock_threshold' => 5,
        ];
    }
}
