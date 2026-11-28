<?php

namespace Tests\Feature\Pelanggan;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $user, string $number, string $status): Order
    {
        return Order::create([
            'order_number' => $number,
            'user_id' => $user->id,
            'shipping_address' => 'Jl. Contoh No. 1',
            'total' => 500000,
            'status' => $status,
        ]);
    }

    public function test_pelanggan_sees_only_own_orders(): void
    {
        $me = User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'pelanggan', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'pelanggan', 'is_active' => true]);
        $this->makeOrder($me, 'MDV-1', Order::STATUS_MENUNGGU_PEMBAYARAN);
        $this->makeOrder($other, 'MDV-2', Order::STATUS_SELESAI);

        $this->actingAs($me)->get(route('pelanggan.dashboard'))
            ->assertOk()
            ->assertSee('Halo, Siti')
            ->assertSee('MDV-1')
            ->assertDontSee('MDV-2');
    }

    public function test_rejected_payment_shows_reason_and_reupload_action(): void
    {
        $me = User::factory()->create(['role' => 'pelanggan', 'is_active' => true]);
        $order = $this->makeOrder($me, 'MDV-3', Order::STATUS_MENUNGGU_PEMBAYARAN);
        Payment::create([
            'order_id' => $order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Siti',
            'amount' => 500000,
            'status' => Payment::STATUS_DITOLAK,
            'rejection_reason' => 'Foto buram',
        ]);

        $this->actingAs($me)->get(route('pelanggan.dashboard'))
            ->assertOk()
            ->assertSee('Foto buram')
            ->assertSee('Upload Ulang Bukti');
    }

    public function test_guest_and_admin_cannot_open_dashboard(): void
    {
        $this->get(route('pelanggan.dashboard'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('pelanggan.dashboard'))->assertForbidden();
    }
}