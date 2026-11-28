@extends('layouts.guest')
@section('title', 'Status Pesanan - MODEVA')
@section('content')
<div class="w-full max-w-2xl mx-auto px-4"><a href="{{ route('pelanggan.orders.history') }}" class="text-sm underline">← Kembali ke riwayat</a><div class="bg-white border rounded-2xl p-7 mt-5"><p class="uppercase tracking-widest text-xs text-stone-500">Pelacakan Pesanan</p><h1 class="font-serif text-3xl mt-2">{{ $order->order_number }}</h1><p class="mt-4 inline-flex rounded-full bg-stone-100 px-4 py-2 text-sm font-medium">{{ $order->status_label }}</p>
<div class="border-t mt-6 pt-5 space-y-3 text-sm"><p><b>Alamat pengiriman:</b> {{ $order->shipping_address ?? '-' }}</p><p><b>Kurir:</b> {{ $order->shipment?->courier ?? 'Belum ditentukan' }}</p><p><b>Nomor resi:</b> {{ $order->shipment?->tracking_number ?? 'Belum tersedia' }}</p><p><b>Tanggal dikirim:</b> {{ $order->shipment?->shipped_at?->format('d M Y H:i') ?? '-' }}</p><p><b>Tanggal diterima:</b> {{ $order->shipment?->delivered_at?->format('d M Y H:i') ?? '-' }}</p></div></div></div>
@endsection
