@extends('layouts.admin')

@section('title', 'Tinjau Pembayaran - Dasbor Modeva')

@section('content')
@php
    $orderNumber = $payment->order?->order_number ?? ('MDV-' . str_pad($payment->order_id, 6, '0', STR_PAD_LEFT));
    $customerName = $payment->order?->user?->name ?? $payment->sender_name;
    $orderTotal = $payment->order?->total ?? $payment->amount;
    $isAmountMismatch = $payment->order && ($payment->amount !== $payment->order->total);
@endphp

<div class="space-y-6">
    <x-admin.page-header
        title="Tinjau Pembayaran"
        :subtitle="'Pembayaran untuk pesanan ' . $orderNumber"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.payments.index')" variant="secondary" icon="arrow-left">
                Kembali ke Daftar
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    {{-- Peringatan Selisih Nominal Transfer --}}
    @if ($isAmountMismatch)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 flex items-start gap-3">
            <x-icon name="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" strokeWidth="2" />
            <div>
                <p class="font-semibold">Peringatan: Selisih Nominal Pembayaran</p>
                <p class="text-xs text-amber-800 mt-0.5">
                    Nominal yang ditransfer ({{ $payment->formatted_amount }}) tidak sama persis dengan total tagihan pesanan ({{ \App\Support\Rupiah::format($orderTotal) }}). Mohon periksa bukti transfer dengan teliti sebelum memverifikasi.
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri: Informasi Pesanan & Transfer (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Panel Ringkasan Pesanan --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#F3EFEA]">
                    <h2 class="font-serif text-xl font-semibold text-[#1C1917]">Ringkasan Pesanan</h2>
                    <span class="text-xs font-mono text-[#78716C]">{{ $orderNumber }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-[#78716C]">Pelanggan</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5">{{ $customerName }}</dd>
                        @if ($payment->order?->user?->email)
                            <dd class="text-xs text-[#78716C]">{{ $payment->order->user->email }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Total Tagihan Pesanan</dt>
                        <dd class="font-semibold text-[#1C1917] text-base mt-0.5">
                            {{ \App\Support\Rupiah::format($orderTotal) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Waktu Dibuat</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5">
                            {{ $payment->order?->created_at?->format('d M Y, H:i') ?? $payment->created_at->format('d M Y, H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Status Pesanan Saat Ini</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5 capitalize">
                            {{ str_replace('_', ' ', $payment->order?->status ?? 'menunggu_verifikasi') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Panel Detail Transfer Bank --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#F3EFEA]">
                    <h2 class="font-serif text-xl font-semibold text-[#1C1917]">Detail Transfer Bank</h2>
                    <x-admin.status-badge
                        :variant="$payment->status_variant"
                        :label="$payment->status_label"
                    />
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-[#78716C]">Bank Tujuan / Pengirim</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5">{{ $payment->bank_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Nama Rekening Pengirim</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5">{{ $payment->sender_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Nominal Ditransfer</dt>
                        <dd class="font-bold text-[#1C1917] text-lg mt-0.5">
                            {{ $payment->formatted_amount }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[#78716C]">Waktu Bukti Diunggah</dt>
                        <dd class="font-medium text-[#1C1917] mt-0.5">
                            {{ $payment->uploaded_at?->format('d M Y, H:i') ?? '-' }}
                        </dd>
                    </div>
                </dl>

                {{-- Riwayat Verifikasi --}}
                @if ($payment->verified_by || $payment->rejection_reason)
                    <div class="pt-4 border-t border-[#F3EFEA] text-xs space-y-1.5 bg-[#FAF8F5] p-3.5 rounded-xl">
                        @if ($payment->verifier)
                            <p class="text-[#78716C]">
                                Diverifikasi oleh: <span class="font-medium text-[#1C1917]">{{ $payment->verifier->name }}</span>
                                pada {{ $payment->verified_at?->format('d M Y, H:i') }}
                            </p>
                        @endif

                        @if ($payment->rejection_reason)
                            <div>
                                <span class="font-semibold text-rose-700">Alasan Penolakan:</span>
                                <p class="text-rose-900 mt-0.5 italic bg-rose-50 p-2.5 rounded-lg border border-rose-100">
                                    "{{ $payment->rejection_reason }}"
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Bukti Transfer & Tombol Aksi (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <h2 class="font-serif text-xl font-semibold text-[#1C1917]">Bukti Transfer</h2>

                @if ($payment->proof_path)
                    <div class="space-y-3">
                        <div class="relative rounded-2xl overflow-hidden border border-[#EFECE6] bg-[#FAF8F5] flex items-center justify-center p-2 min-h-64">
                            <img
                                src="{{ route('admin.payments.proof', $payment) }}"
                                alt="Bukti Transfer {{ $orderNumber }}"
                                class="max-h-80 w-auto object-contain rounded-xl"
                            >
                        </div>
                        <div class="text-center">
                            <a
                                href="{{ route('admin.payments.proof', $payment) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-[#1C1917] hover:underline"
                            >
                                <x-icon name="eye" class="w-3.5 h-3.5" strokeWidth="2" />
                                Buka bukti ukuran penuh di tab baru
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-[#78716C] bg-[#FAF8F5] rounded-2xl border border-dashed border-[#D0C5B8] space-y-2">
                        <x-icon name="alert-triangle" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="text-xs font-medium text-[#1C1917]">Belum ada file bukti transfer</p>
                        <p class="text-[11px] text-[#78716C]">Pelanggan belum mengunggah struk/tangkapan layar transfer bank.</p>
                    </div>
                @endif

                {{-- Formulir Aksi Verifikasi / Tolak --}}
                @if ($payment->isPendingVerification())
                    <div class="pt-4 border-t border-[#F3EFEA] space-y-3">
                        {{-- Form Verifikasi --}}
                        <form
                            method="POST"
                            action="{{ route('admin.payments.verify', $payment) }}"
                            onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini? Status pesanan akan otomatis berubah menjadi diproses.');"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium text-white bg-[#222222] hover:bg-[#33312E] transition-all shadow-2xs cursor-pointer"
                            >
                                <x-icon name="check-circle" class="w-4 h-4" strokeWidth="2" />
                                Verifikasi Pembayaran
                            </button>
                        </form>

                        {{-- Tombol Buka Form Penolakan --}}
                        <details class="group">
                            <summary class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 transition-all cursor-pointer select-none">
                                <x-icon name="x-circle" class="w-4 h-4" strokeWidth="2" />
                                Tolak Pembayaran
                            </summary>

                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="mt-4 p-4 rounded-2xl bg-rose-50/50 border border-rose-200 space-y-3">
                                @csrf
                                <div>
                                    <label for="rejection_reason" class="block text-xs font-semibold text-rose-900 mb-1">
                                        Alasan Penolakan <span class="text-rose-600">*</span>
                                    </label>
                                    <textarea
                                        name="rejection_reason"
                                        id="rejection_reason"
                                        rows="3"
                                        required
                                        minlength="5"
                                        placeholder="Tuliskan alasan penolakan agar pelanggan dapat mengunggah bukti yang benar..."
                                        class="block w-full px-3 py-2 text-xs bg-white text-[#1C1917] placeholder-slate-400 rounded-xl border border-rose-200 focus:outline-none focus:ring-1 focus:ring-rose-500 shadow-2xs"
                                    >{{ old('rejection_reason') }}</textarea>
                                    @error('rejection_reason')
                                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-full transition-colors cursor-pointer"
                                >
                                    Konfirmasi Tolak Pembayaran
                                </button>
                            </form>
                        </details>
                    </div>
                @else
                    <div class="pt-4 border-t border-[#F3EFEA] text-center text-xs text-[#78716C]">
                        Status pembayaran ini sudah final ({{ $payment->status_label }}).
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
