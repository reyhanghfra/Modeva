@extends('layouts.petugas')
@section('title', 'Pengelolaan Pengiriman - MODEVA')
@section('content')
<div class="space-y-6">
    <div><h1 class="font-serif text-3xl font-semibold">Pengelolaan Pengiriman</h1><p class="text-sm text-stone-500 mt-1">Atur kurir, nomor resi, dan perkembangan pesanan.</p></div>
    @if(session('success'))<div class="bg-emerald-50 text-emerald-800 p-3 rounded-xl">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-rose-50 text-rose-800 p-3 rounded-xl">{{ $errors->first() }}</div>@endif
    <form method="GET" class="flex gap-3 flex-wrap bg-white border rounded-2xl p-4"><input name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan / pelanggan" class="border rounded-lg px-3 py-2 flex-1"><select name="status" class="border rounded-lg px-3 py-2"><option value="">Semua status</option>@foreach(['diproses'=>'Diproses','dikirim'=>'Dikirim','selesai'=>'Selesai'] as $v=>$label)<option value="{{ $v }}" @selected(request('status')===$v)>{{ $label }}</option>@endforeach</select><button class="bg-stone-900 text-white rounded-lg px-4">Filter</button></form>
    <div class="space-y-4">@forelse($orders as $order)<article class="bg-white border rounded-2xl p-5"><div class="flex flex-wrap justify-between gap-3"><div><p class="font-mono font-semibold">{{ $order->order_number }}</p><p class="text-sm text-stone-500">{{ $order->user?->name }} · {{ $order->created_at->format('d M Y') }}</p></div><span class="text-sm font-semibold">{{ $order->status_label }}</span></div>
    @if(in_array($order->status,['diproses','dikirim']))<form method="POST" action="{{ route('petugas.shipments.ship',$order) }}" class="grid sm:grid-cols-3 gap-3 mt-5">@csrf<input name="courier" value="{{ $order->shipment?->courier }}" required placeholder="Kurir (JNE, J&T, SiCepat)" class="border rounded-lg px-3 py-2"><input name="tracking_number" value="{{ $order->shipment?->tracking_number }}" required placeholder="Nomor resi" class="border rounded-lg px-3 py-2"><button class="bg-stone-900 text-white rounded-lg px-4 py-2">{{ $order->status==='dikirim'?'Perbarui Resi':'Simpan & Tandai Dikirim' }}</button></form>@endif
    @if($order->shipment)<p class="text-sm text-stone-600 mt-3">{{ $order->shipment->courier }} · Resi: {{ $order->shipment->tracking_number }} · Dikirim: {{ $order->shipment->shipped_at?->format('d M Y H:i') ?? '-' }}</p>@endif
    @if($order->status==='dikirim')<form method="POST" action="{{ route('petugas.shipments.complete',$order) }}" class="mt-4">@csrf @method('PATCH')<button class="text-emerald-800 underline">Tandai pesanan selesai</button></form>@endif
    </article>@empty<div class="bg-white border rounded-2xl p-10 text-center text-stone-500">Tidak ada pesanan pada tahap pengiriman.</div>@endforelse</div>
    {{ $orders->links() }}
</div>
@endsection
