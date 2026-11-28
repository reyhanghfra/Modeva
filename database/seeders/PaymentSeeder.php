<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PaymentSeeder extends Seeder
{
    /**
     * Menjalankan seeder verifikasi pembayaran jika tabel orders sudah tersedia.
     * Idempotent dan membuat contoh pembayaran untuk pesanan yang ada serta pelanggan@modeva.test.
     */
    public function run(): void
    {
        if (! Schema::hasTable('orders') || Order::count() === 0) {
            // Lewati tanpa error jika tabel orders belum dibuat oleh modul Pesanan (Orang 3)
            return;
        }

        // 1. Seed payments untuk pesanan yang sudah ada (misal dari OrderSeeder)
        $orders = Order::all();
        $statuses = [
            Payment::STATUS_MENUNGGU_VERIFIKASI,
            Payment::STATUS_TERVERIFIKASI,
            Payment::STATUS_DITOLAK,
            Payment::STATUS_BELUM_DIBAYAR,
        ];

        foreach ($orders as $index => $order) {
            $status = match ($order->status) {
                Order::STATUS_VERIFIKASI_PEMBAYARAN => Payment::STATUS_MENUNGGU_VERIFIKASI,
                Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM, Order::STATUS_SELESAI => Payment::STATUS_TERVERIFIKASI,
                Order::STATUS_DIBATALKAN => Payment::STATUS_DITOLAK,
                default => $statuses[$index % count($statuses)],
            };

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => 'transfer',
                    'bank_name' => 'BCA',
                    'sender_name' => $order->user?->name ?? 'Pelanggan Modeva',
                    'amount' => $order->total ?? 500000,
                    'proof_path' => null,
                    'status' => $status,
                    'rejection_reason' => $status === Payment::STATUS_DITOLAK ? 'Bukti transfer tidak jelas/buram.' : null,
                    'uploaded_at' => $status !== Payment::STATUS_BELUM_DIBAYAR ? now()->subHours(2) : null,
                    'verified_by' => $status === Payment::STATUS_TERVERIFIKASI ? 1 : null,
                    'verified_at' => $status === Payment::STATUS_TERVERIFIKASI ? now() : null,
                ]
            );
        }

        // 2. Buat 2-3 payment contoh khusus untuk pelanggan@modeva.test dengan status berbeda
        $pelanggan = User::where('email', 'pelanggan@modeva.test')->first();
        if ($pelanggan) {
            $pelangganOrders = Order::where('user_id', $pelanggan->id)->take(3)->get();

            // Jika pelanggan@modeva.test belum memiliki pesanan, buat 3 pesanan contoh untuknya
            if ($pelangganOrders->count() < 3) {
                $needed = 3 - $pelangganOrders->count();
                for ($i = 1; $i <= $needed; $i++) {
                    Order::create([
                        'order_number' => 'MDV-PLG-' . str_pad($pelanggan->id . $i, 5, '0', STR_PAD_LEFT),
                        'user_id' => $pelanggan->id,
                        'shipping_address' => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
                        'total' => 450000 * $i,
                        'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
                    ]);
                }
                $pelangganOrders = Order::where('user_id', $pelanggan->id)->take(3)->get();
            }

            $customerStatuses = [
                Payment::STATUS_BELUM_DIBAYAR,
                Payment::STATUS_MENUNGGU_VERIFIKASI,
                Payment::STATUS_DITOLAK,
            ];

            foreach ($pelangganOrders as $idx => $pOrder) {
                $cStatus = $customerStatuses[$idx % count($customerStatuses)];
                Payment::updateOrCreate(
                    ['order_id' => $pOrder->id],
                    [
                        'method' => 'transfer',
                        'bank_name' => 'BCA',
                        'sender_name' => $pelanggan->name,
                        'amount' => $pOrder->total ?? 450000,
                        'proof_path' => null,
                        'status' => $cStatus,
                        'rejection_reason' => $cStatus === Payment::STATUS_DITOLAK ? 'Bukti transfer tidak jelas/buram.' : null,
                        'uploaded_at' => $cStatus !== Payment::STATUS_BELUM_DIBAYAR ? now()->subHours(2) : null,
                        'verified_by' => null,
                        'verified_at' => null,
                    ]
                );
            }
        }
    }
}
