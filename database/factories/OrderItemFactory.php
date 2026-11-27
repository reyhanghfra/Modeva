<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(50000, 500000);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'product_name' => fake()->words(3, true),
            'variant' => null,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ];
    }
}
