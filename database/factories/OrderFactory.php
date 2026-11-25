<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'MDV-' . fake()->unique()->numerify('######'),
            'user_id' => User::factory()->state(['role' => 'pelanggan']),
            'shipping_address' => fake()->address(),
            'total' => fake()->numberBetween(150000, 2000000),
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ];
    }
}
