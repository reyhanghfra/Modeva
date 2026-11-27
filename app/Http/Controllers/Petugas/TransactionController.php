<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi yang perlu ditangani oleh petugas.
     */
    public function index(): View
    {
        $transactions = collect();

        if (Schema::hasTable('orders')) {
            $transactions = Order::query()
                ->needsPetugasAction()
                ->with(['user', 'latestPayment'])
                ->orderByRaw("CASE WHEN status = 'verifikasi_pembayaran' THEN 0 ELSE 1 END")
                ->latest('id')
                ->get();
        }

        $totalTransactions = $transactions->count();

        return view('petugas.transactions.index', compact('transactions', 'totalTransactions'));
    }

    /**
     * Menampilkan detail pesanan yang berada dalam kewenangan petugas.
     */
    public function show(Request $request, Order $order): View|Response
    {
        if (! $order->canBeHandledByPetugas()) {
            return response()->view('errors.403', [
                'user' => $request->user(),
            ], 403);
        }

        $order->load(['user.customer', 'items', 'payments.verifier']);

        return view('petugas.transactions.show', compact('order'));
    }
}
