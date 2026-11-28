<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
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
        return view('petugas.shipments.index', compact('orders'));
    }

    public function ship(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'courier' => ['required', 'string', 'max:80'],
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);
        DB::transaction(function () use ($order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM], true)) {
                throw ValidationException::withMessages(['status' => 'Pesanan belum berada pada tahap yang dapat dikirim.']);
            }
            Shipment::updateOrCreate(['order_id' => $locked->id], [
                'courier' => $data['courier'], 'tracking_number' => $data['tracking_number'],
                'shipped_at' => $locked->status === Order::STATUS_DIPROSES ? now() : ($locked->shipment?->shipped_at ?? now()),
            ]);
            $locked->update(['status' => Order::STATUS_DIKIRIM]);
        });
        return back()->with('success', 'Kurir dan nomor resi tersimpan. Status pesanan menjadi dikirim.');
    }

    public function complete(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $shipment = Shipment::where('order_id', $locked->id)->first();
            if ($locked->status !== Order::STATUS_DIKIRIM || ! $shipment) {
                throw ValidationException::withMessages(['status' => 'Pesanan harus sudah dikirim dan memiliki data pengiriman.']);
            }
            $shipment->update(['delivered_at' => now()]);
            $locked->update(['status' => Order::STATUS_SELESAI]);
        });
        return back()->with('success', 'Pesanan ditandai selesai.');
    }
}
