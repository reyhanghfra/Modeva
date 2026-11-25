<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar seluruh transaksi pelanggan dengan pencarian, filter, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Order::query()->with(['user', 'latestPayment']);

        // Pencarian berdasarkan nomor transaksi atau nama customer
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter berdasarkan status pesanan
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter berdasarkan rentang tanggal transaksi
        if ($request->filled('start_date')) {
            try {
                $startDate = Carbon::parse((string) $request->input('start_date'))->startOfDay();
                $query->where('created_at', '>=', $startDate);
            } catch (\Throwable) {
                // Abaikan format tanggal mulai yang tidak valid
            }
        }

        if ($request->filled('end_date')) {
            try {
                $endDate = Carbon::parse((string) $request->input('end_date'))->endOfDay();
                $query->where('created_at', '<=', $endDate);
            } catch (\Throwable) {
                // Abaikan format tanggal akhir yang tidak valid
            }
        }

        $transactions = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalTransactions = Order::count();

        return view('admin.transactions.index', compact('transactions', 'totalTransactions'));
    }

    /**
     * Menampilkan halaman detail transaksi beserta item produk, alamat pengiriman, dan pembayaran.
     */
    public function show(Order $order): View
    {
        $order->load(['user.customer', 'items', 'payments.verifier']);

        return view('admin.transactions.show', compact('order'));
    }
}
