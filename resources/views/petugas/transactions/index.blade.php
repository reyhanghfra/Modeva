@extends('layouts.petugas')

@section('title', 'Data Transaksi Petugas - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Data Transaksi"
        :subtitle="$totalTransactions . ' transaksi yang perlu ditangani oleh petugas'"
    >
        <x-slot:action>
            <x-admin.button :href="route('petugas.dashboard')" variant="secondary" icon="arrow-left">
                Kembali ke Dasbor
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Tabel Daftar Transaksi Petugas --}}
    <x-admin.data-table
        :headers="[
            'Nomor Transaksi',
            'Nama Pelanggan',
            'Tanggal Pesanan',
            'Total Pembayaran',
            'Status Pesanan',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
    >
        @forelse ($transactions as $transaction)
            @php
                $payment = $transaction->latestPayment;
                $actionHint = $transaction->status === \App\Models\Order::STATUS_VERIFIKASI_PEMBAYARAN
                    ? 'Perlu verifikasi pembayaran'
                    : 'Perlu pengemasan & pengiriman';
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors">
                {{-- Nomor Transaksi --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <span class="font-mono text-xs sm:text-sm font-semibold text-[#1C1917]">
                        {{ $transaction->order_number }}
                    </span>
                </td>

                {{-- Nama Pelanggan --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="font-medium text-[#1C1917]">
                        {{ $transaction->user?->name ?? '-' }}
                    </div>
                    @if ($transaction->user?->email)
                        <div class="text-xs text-[#78716C] mt-0.5">
                            {{ $transaction->user->email }}
                        </div>
                    @endif
                </td>

                {{-- Tanggal Pesanan --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#78716C] whitespace-nowrap">
                    {{ $transaction->created_at?->format('d M Y, H:i') ?? '-' }}
                </td>

                {{-- Total Pembayaran --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-sm text-[#1C1917]">
                        {{ $transaction->formatted_total }}
                    </span>
                </td>

                {{-- Status Pesanan & Kejelasan Status Transaksi --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="flex flex-col items-start gap-1">
                        <x-admin.status-badge
                            :variant="$transaction->status_variant"
                            :label="$transaction->status_label"
                        />
                        <span class="text-[11px] font-medium text-[#8C5E28]">
                            {{ $actionHint }}
                            @if ($payment)
                                &middot; Pembayaran: {{ $payment->status_label }}
                            @endif
                        </span>
                    </div>
                </td>

                {{-- Tombol Detail Pesanan --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <x-admin.button
                        :href="route('petugas.transactions.show', $transaction)"
                        variant="secondary"
                        size="sm"
                        icon="eye"
                    >
                        Detail Pesanan
                    </x-admin.button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="check-circle" class="w-8 h-8 text-emerald-500 mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Tidak ada transaksi perlu ditangani</p>
                        <p class="text-xs text-[#78716C]">Saat ini belum ada pesanan yang memerlukan verifikasi atau pengemasan oleh petugas.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
