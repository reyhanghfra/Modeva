@extends('layouts.petugas')
@section('title', 'Detail Pembayaran - MODEVA')
@section('content')
<div class="max-w-3xl space-y-6">
    <a href="{{ route('petugas.payments.index') }}" class="text-sm underline">← Kembali ke pembayaran</a>
    <div><h1 class="font-serif text-3xl font-semibold">Detail Pembayaran</h1><p class="text-stone-500">Pesanan {{ $payment->order?->order_number ?? '-' }}</p></div>
    @if(session('success'))<div class="bg-emerald-50 text-emerald-800 p-3 rounded-xl">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-rose-50 text-rose-800 p-3 rounded-xl">{{ session('error') }}</div>@endif
    <div class="bg-white border rounded-2xl p-6 space-y-3"><p><b>Pelanggan:</b> {{ $payment->order?->user?->name ?? '-' }}</p><p><b>Bank:</b> {{ $payment->bank_name }}</p><p><b>Nama pengirim:</b> {{ $payment->sender_name }}</p><p><b>Nominal:</b> {{ $payment->formatted_amount }}</p><p><b>Status:</b> {{ $payment->status_label }}</p><p><b>Diunggah:</b> {{ $payment->uploaded_at?->format('d M Y H:i') ?? '-' }}</p>
    @if($payment->proof_path)<a class="inline-block underline text-amber-800" target="_blank" href="{{ route('admin.payments.proof',$payment) }}">Lihat bukti transfer</a>@endif
    @if($payment->isPendingVerification())<div class="border-t pt-5 flex flex-wrap gap-3"><form method="POST" action="{{ route('petugas.payments.verify',$payment) }}">@csrf<button class="bg-emerald-700 text-white px-5 py-2 rounded-lg">Verifikasi pembayaran</button></form><form method="POST" action="{{ route('petugas.payments.reject',$payment) }}" class="flex flex-wrap gap-2">@csrf<input name="rejection_reason" required minlength="5" maxlength="500" placeholder="Alasan penolakan" class="border rounded-lg px-3 py-2"><button class="bg-rose-700 text-white px-5 py-2 rounded-lg">Tolak</button></form></div>@elseif($payment->rejection_reason)<p class="text-rose-700">Alasan ditolak: {{ $payment->rejection_reason }}</p>@endif
    </div>
</div>
@endsection
