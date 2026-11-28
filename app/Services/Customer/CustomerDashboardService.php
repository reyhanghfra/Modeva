<?php

namespace App\Services\Customer;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomerDashboardService
{
    /**
     * Menghitung jumlah pesanan milik pengguna berdasarkan kelompok status.
     *
     * @return array<string, int>
     */
    public function countsByStatus(User $user): array
    {
        $defaultCounts = [
            'menunggu_pembayaran' => 0,
            'menunggu_verifikasi' => 0,
            'dalam_proses' => 0,
            'selesai' => 0,
        ];

        if (! Schema::hasTable('orders')) {
            return $defaultCounts;
        }

        $userOrders = Order::where('user_id', $user->id)->get(['id', 'status']);

        return [
            'menunggu_pembayaran' => $userOrders->where('status', Order::STATUS_MENUNGGU_PEMBAYARAN)->count(),
            'menunggu_verifikasi' => $userOrders->where('status', Order::STATUS_VERIFIKASI_PEMBAYARAN)->count(),
            'dalam_proses' => $userOrders->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM])->count(),
            'selesai' => $userOrders->where('status', Order::STATUS_SELESAI)->count(),
        ];
    }

    /**
     * Mendapatkan daftar pesanan yang memerlukan tindakan segera dari pelanggan.
     * Meliputi pesanan 'menunggu_pembayaran' dan pesanan yang pembayarannya 'ditolak'.
     *
     * @return Collection<int, array>
     */
    public function needsAction(User $user): Collection
    {
        if (! Schema::hasTable('orders')) {
            return collect();
        }

        // Ambil pesanan milik user
        $userOrders = Order::where('user_id', $user->id)
            ->latest('id')
            ->get();

        if ($userOrders->isEmpty()) {
            return collect();
        }

        // Ambil data Payment dengan whereIn('order_id', ...) satu kali untuk menghindari N+1 query
        $orderIds = $userOrders->pluck('id')->all();
        $payments = Schema::hasTable('payments')
            ? Payment::whereIn('order_id', $orderIds)
                ->latest('id')
                ->get()
                ->keyBy('order_id')
            : collect();

        $deadlineHours = config('payment.deadline_hours', 24);
        $actionItems = collect();

        foreach ($userOrders as $order) {
            /** @var Payment|null $payment */
            $payment = $payments->get($order->id);

            $isRejected = $payment && $payment->status === Payment::STATUS_DITOLAK;
            $isWaitingPayment = $order->status === Order::STATUS_MENUNGGU_PEMBAYARAN && (! $payment || $payment->status === Payment::STATUS_BELUM_DIBAYAR);

            if ($isRejected || $isWaitingPayment) {
                $deadline = $order->created_at ? $order->created_at->copy()->addHours($deadlineHours) : now()->addHours($deadlineHours);

                $actionItems->push([
                    'order' => $order,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'deadline' => $deadline,
                    'is_rejected' => $isRejected,
                    'rejection_reason' => $isRejected ? ($payment->rejection_reason ?? 'Bukti transfer tidak sesuai.') : null,
                    'action_label' => $isRejected ? 'Upload Ulang Bukti' : 'Bayar Sekarang',
                    'action_url' => $isRejected
                        ? route('pelanggan.payments.upload', $order->order_number)
                        : route('pelanggan.payments.show', $order->order_number),
                ]);
            }
        }

        return $actionItems;
    }

    /**
     * Mendapatkan maksimal 5 pesanan terbaru milik pengguna.
     *
     * @return Collection<int, Order>
     */
    public function recentOrders(User $user): Collection
    {
        if (! Schema::hasTable('orders')) {
            return collect();
        }

        return Order::where('user_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get();
    }
}
