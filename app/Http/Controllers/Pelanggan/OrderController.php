<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['items', 'latestPayment', 'shipment'])
            ->where('user_id', $request->user()->id)->latest('id')->paginate(10);
        return view('pelanggan.pesanan.index', compact('orders'));
    }

    public function track(Request $request): View
    {
        $data = $request->validate(['order_number' => ['required', 'string', 'max:100']]);
        $order = Order::with(['items', 'latestPayment', 'shipment'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $data['order_number'])->firstOrFail();
        return view('pelanggan.pesanan.track', compact('order'));
    }
}
