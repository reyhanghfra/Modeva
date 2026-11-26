<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('MDV-???-###')),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'brand' => fake()->randomElement([
                'Kala Studio',
                'Sora',
                'Arunika',
                'Lumen',
                'Nusantara Atelier',
            ]),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 50000, 2000000),
            'image' => 'products/'.Str::slug($name).'.jpg',
            'is_active' => true,
        ];
    }
}
