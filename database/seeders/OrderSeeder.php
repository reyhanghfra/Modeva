<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Menjalankan seeder transaksi pelanggan secara idempotent.
     */
    public function run(): void
    {
        $ordersData = [
            [
                'order_number' => 'MDV-250607',
                'customer_email' => 'bagas@modeva.test',
                'shipping_address' => 'Jl. Pandanaran No. 27, Semarang',
                'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
                'items' => [
                    [
                        'product_name' => 'Kemeja Linen Anak Laki-Laki Pasir',
                        'variant' => 'Krem · 8 Tahun',
                        'quantity' => 2,
                        'price' => 225000,
                    ],
                    [
                        'product_name' => 'Celana Chino Anak Katun Twill',
                        'variant' => 'Khaki · 8 Tahun',
                        'quantity' => 1,
                        'price' => 200000,
                    ],
                ],
            ],
            [
                'order_number' => 'MDV-250608',
                'customer_email' => 'siti@modeva.test',
                'shipping_address' => 'Jl. Sudirman No. 105, Jakarta Pusat',
                'status' => Order::STATUS_VERIFIKASI_PEMBAYARAN,
                'items' => [
                    [
                        'product_name' => 'Dress Midi Linen Kopi',
                        'variant' => 'Cokelat Kopi · M',
                        'quantity' => 1,
                        'price' => 690000,
                    ],
                    [
                        'product_name' => 'Blazer Wol Oversize Malam',
                        'variant' => 'Hitam · M',
                        'quantity' => 1,
                        'price' => 1200000,
                    ],
                ],
            ],
            [
                'order_number' => 'MDV-250609',
                'customer_email' => 'rizky@modeva.test',
                'shipping_address' => 'Jl. Malioboro No. 88, Yogyakarta',
                'status' => Order::STATUS_DIPROSES,
                'items' => [
                    [
                        'product_name' => 'Kaos Polo Rajut Dewasa Pria',
                        'variant' => 'Navy · L',
                        'quantity' => 1,
                        'price' => 420000,
                    ],
                ],
            ],
            [
                'order_number' => 'MDV-250610',
                'customer_email' => 'maya@modeva.test',
                'shipping_address' => 'Jl. Darmo Permai Selatan No. 12, Surabaya',
                'status' => Order::STATUS_DIKIRIM,
                'items' => [
                    [
                        'product_name' => 'Setelan Linen Pasir Dewasa',
                        'variant' => 'Krem · M',
                        'quantity' => 1,
                        'price' => 550000,
                    ],
                    [
                        'product_name' => 'Gaun Katun Anak Perempuan Bunga',
                        'variant' => 'Sage · 6 Tahun',
                        'quantity' => 1,
                        'price' => 300000,
                    ],
                ],
            ],
            [
                'order_number' => 'MDV-250611',
                'customer_email' => 'anindya@modeva.test',
                'shipping_address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
                'status' => Order::STATUS_SELESAI,
                'items' => [
                    [
                        'product_name' => 'Sneakers Kulit Putih Pagi',
                        'variant' => 'Putih · 38',
                        'quantity' => 1,
                        'price' => 850000,
                    ],
                    [
                        'product_name' => 'Cardigan Rajut Anak Hangat',
                        'variant' => 'Oatmeal · 10 Tahun',
                        'quantity' => 2,
                        'price' => 300000,
                    ],
                ],
            ],
        ];

        $fallbackCustomer = User::where('role', 'pelanggan')->first();

        foreach ($ordersData as $data) {
            $customer = User::where('email', $data['customer_email'])->first() ?? $fallbackCustomer;

            if (! $customer) {
                continue;
            }

            $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['price']);

            $order = Order::updateOrCreate(
                ['order_number' => $data['order_number']],
                [
                    'user_id' => $customer->id,
                    'shipping_address' => $data['shipping_address'],
                    'total' => $total,
                    'status' => $data['status'],
                ]
            );

            // Sinkronkan item pesanan secara idempotent
            $order->items()->delete();
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => null,
                    'product_name' => $item['product_name'],
                    'variant' => $item['variant'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }
        }
    }
}
