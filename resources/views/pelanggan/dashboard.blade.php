@extends('layouts.customer')

@section('title', 'Akun Saya - Modeva')

@section('content')
@php
    $ordersUrl = Route::has('pelanggan.orders.history') ? route('pelanggan.orders.history') : (Route::has('pelanggan.orders.index') ? route('pelanggan.orders.index') : '#');
    $catalogUrl = Route::has('katalog.index') ? route('katalog.index') : (Route::has('products.index') ? route('products.index') : '#');
    $stats = [
        ['Menunggu Pembayaran', 'menunggu_pembayaran', 'receipt'],
        ['Menunggu Verifikasi', 'menunggu_verifikasi', 'layers'],
        ['Dalam Proses', 'dalam_proses', 'box'],
        ['Selesai', 'selesai', 'check-circle'],
    ];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
    <div>
        <p class="uppercase tracking-[0.3em] text-xs font-semibold text-[#78716C]">Akun Saya</p>
        <h1 class="font-serif text-4xl sm:text-5xl font-semibold text-[#1C1917] mt-2">Halo, {{ \Illuminate\Support\Str::before($user->name, ' ') }}</h1>
        <p class="text-sm text-[#78716C] mt-2">Pantau pesanan dan pembayaran Anda di sini.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($stats as [$label, $key, $icon])
            <x-stat-card :label="$label" :value="$statusCounts[$key]" :icon="$icon" :href="$ordersUrl !== '#' ? $ordersUrl : null" />
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <x-panel title="Pesanan Terbaru" class="lg:col-span-2 order-2 lg:order-1">
            <x-slot:headerRight>
                @if ($ordersUrl !== '#')
                    <a href="{{ $ordersUrl }}" class="text-sm underline text-[#1C1917]">Lihat semua</a>
                @endif
            </x-slot:headerRight>

            @forelse ($recentOrders as $order)
                <a @if ($order->status === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN) href="{{ route('pelanggan.payments.show', $order->order_number) }}" @endif
                   class="flex items-center justify-between gap-4 py-4 border-t border-[#EFECE6] first:border-t-0">
                    <div class="min-w-0">
                        <p class="font-medium text-sm text-[#1C1917] truncate">{{ $order->order_number }}</p>
                        <p class="text-xs text-[#78716C] mt-0.5">{{ $order->created_at->locale('id')->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="text-right shrink-0 space-y-1">
                        <p class="font-semibold text-sm text-[#1C1917]">{{ $order->formatted_total }}</p>
                        <x-status-badge :variant="$order->status_variant" :label="$order->status_label" />
                    </div>
                </a>
            @empty
                <div class="text-center py-10 text-sm text-[#78716C]">
                    <p>Belum ada pesanan.</p>
                    <a href="{{ $catalogUrl }}" class="inline-block mt-4 px-6 py-2.5 rounded-full bg-[#1C1917] text-white text-sm font-medium hover:bg-black transition-colors">Mulai Belanja</a>
                </div>
            @endforelse
        </x-panel>

        <div class="space-y-6 order-1 lg:order-2">
            <x-panel title="Perlu Tindakan" icon="alert-triangle">
                @forelse ($needsAction as $item)
                    <div class="py-4 border-t border-[#EFECE6] first:border-t-0 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium text-sm text-[#1C1917]">{{ $item['order_number'] }}</span>
                            <span class="font-semibold text-sm text-[#1C1917]">{{ \App\Support\Rupiah::format($item['total']) }}</span>
                        </div>

                        @if ($item['is_rejected'])
                            <x-status-badge variant="danger" label="Pembayaran ditolak" />
                            <p class="text-xs text-[#78716C]">{{ $item['rejection_reason'] }}</p>
                        @else
                            <p class="text-xs text-[#78716C]">Bayar sebelum {{ $item['deadline']->locale('id')->translatedFormat('d F Y, H:i') }}</p>
                        @endif

                        <a href="{{ $item['action_url'] }}" class="inline-block px-5 py-2 rounded-full bg-[#1C1917] text-white text-xs font-medium hover:bg-black transition-colors">{{ $item['action_label'] }}</a>
                    </div>
                @empty
                    <p class="text-sm text-[#78716C]">Tidak ada pembayaran yang perlu ditindaklanjuti.</p>
                @endforelse
            </x-panel>

            <x-panel title="Akun Saya">
                <p class="font-medium text-sm text-[#1C1917]">{{ $user->name }}</p>
                <p class="text-xs text-[#78716C] mt-0.5">{{ $user->email }}</p>
                <p class="text-xs text-[#A8A29E] mt-3">Bergabung sejak {{ $user->created_at->locale('id')->translatedFormat('F Y') }}</p>
            </x-panel>
        </div>
    </div>
</div>
@endsection