@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Verifikasi Pembayaran"
        :subtitle="$pendingCount . ' pembayaran menunggu verifikasi dari total ' . $totalPayments . ' transaksi'"
    />

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Pencarian & Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <x-admin.search-input placeholder="Cari ID pesanan, pelanggan, bank..." />

        <div class="flex items-center gap-3">
            <span class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Status:</span>
            <x-admin.filter-pills
                name="status"
                :options="[
                    '' => 'Semua',
                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                    'belum_dibayar' => 'Belum Dibayar',
                    'terverifikasi' => 'Terverifikasi',
                    'ditolak' => 'Ditolak',
                ]"
            />
        </div>
    </div>

    {{-- Tabel Pembayaran --}}
    <x-admin.data-table
        :headers="[
            'Pesanan & Pelanggan',
            'Waktu Unggah',
            'Nominal Transfer',
            'Bank / Pengirim',
            'Status',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
        :paginator="$payments"
    >
        @forelse ($payments as $payment)
            @php
                $orderNumber = $payment->order?->order_number ?? ('MDV-' . str_pad($payment->order_id, 6, '0', STR_PAD_LEFT));
                $customerName = $payment->order?->user?->name ?? $payment->sender_name;
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors {{ $payment->isPendingVerification() ? 'bg-amber-50/20' : '' }}">
                {{-- ID Pesanan & Nama Pelanggan --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="font-medium text-[#1C1917]">
                        {{ $orderNumber }}
                    </div>
                    <div class="text-xs text-[#78716C] mt-0.5">
                        {{ $customerName }}
                    </div>
                </td>

                {{-- Waktu Unggah --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#78716C] whitespace-nowrap">
                    {{ $payment->uploaded_at?->format('d M Y, H:i') ?? '-' }}
                </td>

                {{-- Nominal Transfer --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-sm text-[#1C1917]">
                        {{ $payment->formatted_amount }}
                    </span>
                    @if ($payment->order && $payment->amount !== $payment->order->total)
                        <span class="block text-[11px] text-amber-700 font-medium">
                            ≠ Tagihan ({{ \App\Support\Rupiah::format($payment->order->total) }})
                        </span>
                    @endif
                </td>

                {{-- Bank & Nama Pengirim --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#1C1917]">
                    <div class="font-medium">{{ $payment->bank_name }}</div>
                    <div class="text-[#78716C] mt-0.5">a.n. {{ $payment->sender_name }}</div>
                </td>

                {{-- Status --}}
                <td class="px-5 sm:px-6 py-4">
                    <x-admin.status-badge
                        :variant="$payment->status_variant"
                        :label="$payment->status_label"
                    />
                </td>

                {{-- Aksi Tinjau --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <x-admin.button
                        :href="route('admin.payments.show', $payment)"
                        variant="secondary"
                        size="sm"
                    >
                        Tinjau
                    </x-admin.button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="credit-card" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Tidak ada data pembayaran</p>
                        <p class="text-xs text-[#78716C]">Belum ada data pembayaran yang sesuai dengan kriteria filter.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
