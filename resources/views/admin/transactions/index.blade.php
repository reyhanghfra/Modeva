@extends('layouts.admin')

@section('title', 'Data Transaksi - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Data Transaksi"
        :subtitle="$totalTransactions . ' transaksi pelanggan tercatat di sistem'"
    />

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Kontrol Pencarian & Filter --}}
    <div class="space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <x-admin.search-input placeholder="Cari nomor transaksi atau nama customer..." />

            {{-- Filter Rentang Tanggal --}}
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-wrap items-center gap-2.5">
                @foreach (request()->except('start_date', 'end_date', 'page') as $param => $val)
                    @if (!is_array($val) && !is_null($val) && $val !== '')
                        <input type="hidden" name="{{ $param }}" value="{{ $val }}">
                    @endif
                @endforeach

                <div class="flex items-center gap-2">
                    <label for="start_date" class="text-xs font-medium text-[#78716C] whitespace-nowrap">Dari:</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ request('start_date') }}"
                        class="px-3 py-1.5 text-xs sm:text-sm bg-white text-[#1C1917] rounded-full border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none shadow-2xs transition-colors"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <label for="end_date" class="text-xs font-medium text-[#78716C] whitespace-nowrap">Sampai:</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ request('end_date') }}"
                        class="px-3 py-1.5 text-xs sm:text-sm bg-white text-[#1C1917] rounded-full border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none shadow-2xs transition-colors"
                    >
                </div>

                <x-admin.button type="submit" variant="primary" size="sm">
                    Terapkan
                </x-admin.button>

                @if (request()->filled('start_date') || request()->filled('end_date'))
                    <x-admin.button
                        :href="request()->fullUrlWithQuery(['start_date' => null, 'end_date' => null, 'page' => null])"
                        variant="secondary"
                        size="sm"
                    >
                        Reset Tanggal
                    </x-admin.button>
                @endif
            </form>
        </div>

        {{-- Filter Status Pesanan --}}
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Status:</span>
            <x-admin.filter-pills
                name="status"
                :options="[
                    '' => 'Semua',
                    \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
                    \App\Models\Order::STATUS_VERIFIKASI_PEMBAYARAN => 'Verifikasi Pembayaran',
                    \App\Models\Order::STATUS_DIPROSES => 'Diproses',
                    \App\Models\Order::STATUS_DIKIRIM => 'Dikirim',
                    \App\Models\Order::STATUS_SELESAI => 'Selesai',
                    \App\Models\Order::STATUS_DIBATALKAN => 'Dibatalkan',
                ]"
            />
        </div>
    </div>

    {{-- Tabel Daftar Transaksi --}}
    <x-admin.data-table
        :headers="[
            'Nomor Transaksi',
            'Nama Customer',
            'Tanggal Transaksi',
            'Total Pembayaran',
            'Metode Pembayaran',
            'Status Pesanan',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
        :paginator="$transactions"
    >
        @forelse ($transactions as $transaction)
            @php
                $payment = $transaction->latestPayment;
                $paymentMethod = $payment
                    ? ucfirst($payment->method) . ($payment->bank_name ? ' (' . $payment->bank_name . ')' : '')
                    : 'Belum ditentukan';
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors">
                {{-- Nomor Transaksi --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <span class="font-mono text-xs sm:text-sm font-semibold text-[#1C1917]">
                        {{ $transaction->order_number }}
                    </span>
                </td>

                {{-- Nama Customer --}}
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

                {{-- Tanggal Transaksi --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#78716C] whitespace-nowrap">
                    {{ $transaction->created_at?->format('d M Y, H:i') ?? '-' }}
                </td>

                {{-- Total Pembayaran --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-sm text-[#1C1917]">
                        {{ $transaction->formatted_total }}
                    </span>
                </td>

                {{-- Metode Pembayaran --}}
                <td class="px-5 sm:px-6 py-4 text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">
                    {{ $paymentMethod }}
                </td>

                {{-- Status Pesanan --}}
                <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                    <x-admin.status-badge
                        :variant="$transaction->status_variant"
                        :label="$transaction->status_label"
                    />
                </td>

                {{-- Tombol Lihat Detail --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <x-admin.button
                        :href="route('admin.transactions.show', $transaction)"
                        variant="secondary"
                        size="sm"
                        icon="eye"
                    >
                        Detail
                    </x-admin.button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="receipt" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Tidak ada transaksi ditemukan</p>
                        <p class="text-xs text-[#78716C]">Belum ada data transaksi atau coba ubah kata kunci pencarian dan filter yang aktif.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
