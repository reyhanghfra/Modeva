<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Shipment;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'shipment'])->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM, Order::STATUS_SELESAI]);
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn ($q) => $q->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $orders = $query->latest('id')->paginate(10)->withQueryString();
        return view('admin.shipments.index', compact('orders'));
    }
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'courier' => ['required', 'string', 'max:80'],
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM], true)) {
                throw ValidationException::withMessages(['status' => 'Pesanan belum berada pada tahap pengiriman.']);
            }
            Shipment::updateOrCreate(['order_id' => $locked->id], [
                'courier' => $data['courier'],
                'tracking_number' => $data['tracking_number'],
                'shipped_at' => $locked->shipment?->shipped_at ?? now(),
            ]);
            $locked->update(['status' => Order::STATUS_DIKIRIM]);
        });

        return back()->with('success', 'Informasi kurir dan resi berhasil diperbarui.');
    }

}
