<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dasbor operasional khusus Petugas berdasarkan data nyata di database.
     */
    public function index(): View
    {
        $hasOrdersTable = Schema::hasTable('orders');
        $hasPaymentsTable = Schema::hasTable('payments');

        $summary = [
            'menunggu_pembayaran' => 0,
            'diproses' => 0,
            'dikirim' => 0,
            'selesai' => 0,
        ];

        $recentOrders = collect();
        $actionRequiredOrders = collect();
        $pendingVerificationCount = 0;

        if ($hasOrdersTable) {
            $statusCounts = Order::query()
                ->selectRaw('status, COUNT(*) as total_count')
                ->whereIn('status', [
                    Order::STATUS_MENUNGGU_PEMBAYARAN,
                    Order::STATUS_DIPROSES,
                    Order::STATUS_DIKIRIM,
                    Order::STATUS_SELESAI,
                ])
                ->groupBy('status')
                ->pluck('total_count', 'status');

            $summary = [
                'menunggu_pembayaran' => (int) ($statusCounts[Order::STATUS_MENUNGGU_PEMBAYARAN] ?? 0),
                'diproses' => (int) ($statusCounts[Order::STATUS_DIPROSES] ?? 0),
                'dikirim' => (int) ($statusCounts[Order::STATUS_DIKIRIM] ?? 0),
                'selesai' => (int) ($statusCounts[Order::STATUS_SELESAI] ?? 0),
            ];

            $recentOrders = Order::query()
                ->with(['user', 'latestPayment'])
                ->latest('id')
                ->take(5)
                ->get();

            $actionRequiredOrders = Order::query()
                ->needsPetugasAction()
                ->with(['user', 'latestPayment'])
                ->orderByRaw("CASE WHEN status = 'verifikasi_pembayaran' THEN 0 ELSE 1 END")
                ->latest('id')
                ->take(6)
                ->get();
        }

        if ($hasPaymentsTable) {
            $pendingVerificationCount = Payment::where('status', Payment::STATUS_MENUNGGU_VERIFIKASI)->count();
        }

        return view('petugas.dashboard', compact(
            'summary',
            'recentOrders',
            'actionRequiredOrders',
            'pendingVerificationCount'
        ));
    }
}
