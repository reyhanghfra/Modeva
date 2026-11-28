@extends('layouts.petugas')
@section('title', 'Verifikasi Pembayaran - MODEVA')
@section('content')
<div class="space-y-6">
    <div><h1 class="font-serif text-3xl font-semibold">Verifikasi Pembayaran</h1><p class="text-sm text-stone-500 mt-1">Periksa bukti transfer dan tentukan status pembayaran pelanggan.</p></div>
    @if(session('success'))<div class="rounded-xl bg-emerald-50 text-emerald-800 p-3">{{ session('success') }}</div>@endif
    <form method="GET" class="flex flex-wrap gap-3 bg-white border rounded-2xl p-4">
        <input name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan, nama, atau bank" class="border rounded-lg px-3 py-2 flex-1 min-w-56">
        <select name="status" class="border rounded-lg px-3 py-2"><option value="">Semua status</option><option value="menunggu_verifikasi" @selected(request('status')==='menunggu_verifikasi')>Menunggu verifikasi</option><option value="terverifikasi" @selected(request('status')==='terverifikasi')>Terverifikasi</option><option value="ditolak" @selected(request('status')==='ditolak')>Ditolak</option></select>
        <button class="bg-stone-900 text-white rounded-lg px-4 py-2">Terapkan</button>
    </form>
    <div class="bg-white border rounded-2xl overflow-x-auto"><table class="w-full text-sm"><thead class="bg-stone-50"><tr><th class="p-4 text-left">Pesanan</th><th class="p-4 text-left">Pelanggan</th><th class="p-4 text-left">Bank / Pengirim</th><th class="p-4 text-left">Nominal</th><th class="p-4 text-left">Status</th><th class="p-4"></th></tr></thead><tbody>
    @forelse($payments as $payment)<tr class="border-t"><td class="p-4 font-mono">{{ $payment->order?->order_number ?? '-' }}</td><td class="p-4">{{ $payment->order?->user?->name ?? '-' }}</td><td class="p-4">{{ $payment->bank_name }}<div class="text-stone-500">{{ $payment->sender_name }}</div></td><td class="p-4">{{ $payment->formatted_amount }}</td><td class="p-4">{{ $payment->status_label }}</td><td class="p-4 text-right"><a class="underline" href="{{ route('petugas.payments.show',$payment) }}">Periksa</a></td></tr>
    @empty<tr><td colspan="6" class="p-8 text-center text-stone-500">Belum ada data pembayaran.</td></tr>@endforelse
    </tbody></table><div class="p-4">{{ $payments->links() }}</div></div>
</div>
@endsection
