<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PaymentPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat berkas bukti transfer privat.
     * Mengizinkan role admin, petugas, serta pelanggan pemilik pesanan.
     */
    public function viewProof(User $user, Payment $payment): bool
    {
        // Admin dan petugas berhak memeriksa semua bukti pembayaran
        if (in_array($user->role, ['admin', 'petugas'], true)) {
            return true;
        }

        // Pelanggan hanya diizinkan melihat berkas bukti untuk pesanan miliknya sendiri
        if ($user->role === 'pelanggan') {
            if ($payment->relationLoaded('order') && $payment->order) {
                return (int) $payment->order->user_id === (int) $user->id;
            }

            if (Schema::hasTable('orders')) {
                $orderUserId = Order::where('id', $payment->order_id)->value('user_id');
                return $orderUserId !== null && (int) $orderUserId === (int) $user->id;
            }
        }

        return false;
    }
}
