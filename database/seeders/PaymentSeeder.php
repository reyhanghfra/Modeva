<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PaymentSeeder extends Seeder
{
    /**
     * Menjalankan seeder verifikasi pembayaran jika tabel orders sudah tersedia.
     */
    public function run(): void
    {
        if (Schema::hasTable('orders') && Order::count() > 0) {
            $orders = Order::take(5)->get();

            $statuses = [
                Payment::STATUS_MENUNGGU_VERIFIKASI,
                Payment::STATUS_MENUNGGU_VERIFIKASI,
                Payment::STATUS_TERVERIFIKASI,
                Payment::STATUS_DITOLAK,
                Payment::STATUS_BELUM_DIBAYAR,
            ];

            foreach ($orders as $index => $order) {
                $status = $statuses[$index % count($statuses)];

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
                        'uploaded_at' => now(),
                        'verified_by' => $status === Payment::STATUS_TERVERIFIKASI ? 1 : null,
                        'verified_at' => $status === Payment::STATUS_TERVERIFIKASI ? now() : null,
                    ]
                );
            }
        } else {
            // Jika tabel orders belum dibuat oleh Orang 3, buat sampel data pembayaran dummy
            $samples = [
                [
                    'order_id' => 1,
                    'method' => 'transfer',
                    'bank_name' => 'BCA',
                    'sender_name' => 'Citra Lestari',
                    'amount' => 450000,
                    'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
                    'uploaded_at' => now()->subHours(2),
                ],
                [
                    'order_id' => 2,
                    'method' => 'transfer',
                    'bank_name' => 'Bank Mandiri',
                    'sender_name' => 'Budi Santoso',
                    'amount' => 899000,
                    'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
                    'uploaded_at' => now()->subHour(),
                ],
                [
                    'order_id' => 3,
                    'method' => 'transfer',
                    'bank_name' => 'BRI',
                    'sender_name' => 'Dewi Anggraini',
                    'amount' => 320000,
                    'status' => Payment::STATUS_TERVERIFIKASI,
                    'verified_by' => 1,
                    'verified_at' => now()->subDay(),
                    'uploaded_at' => now()->subDays(2),
                ],
                [
                    'order_id' => 4,
                    'method' => 'transfer',
                    'bank_name' => 'BNI',
                    'sender_name' => 'Ahmad Fauzi',
                    'amount' => 615000,
                    'status' => Payment::STATUS_DITOLAK,
                    'rejection_reason' => 'Nominal transfer tidak sesuai dengan tagihan pesanan.',
                    'verified_by' => 1,
                    'verified_at' => now()->subDays(3),
                    'uploaded_at' => now()->subDays(3),
                ],
            ];

            foreach ($samples as $sample) {
                Payment::updateOrCreate(
                    ['order_id' => $sample['order_id']],
                    $sample
                );
            }
        }
    }
}
