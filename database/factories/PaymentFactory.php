<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => 1,
            'method' => 'transfer',
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI']),
            'sender_name' => fake()->name(),
            'amount' => fake()->numberBetween(100000, 2000000),
            'proof_path' => null,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'rejection_reason' => null,
            'uploaded_at' => now(),
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'uploaded_at' => now(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_TERVERIFIKASI,
            'verified_at' => now(),
        ]);
    }

    public function rejected(?string $reason = 'Bukti transfer tidak terbaca'): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_DITOLAK,
            'rejection_reason' => $reason,
        ]);
    }
}
