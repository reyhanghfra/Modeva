@extends('layouts.admin')

@section('title', 'Detail Pelanggan - Dasbor Modeva')

@section('content')
@php
    $initials = collect(explode(' ', $customer->name))
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->join('');
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Detail Pelanggan"
        :subtitle="'Informasi lengkap data pelanggan ' . $customer->name"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.customers.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
            <x-admin.button :href="route('admin.customers.edit', $customer)" variant="primary" icon="pencil">
                Edit Pelanggan
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Kartu Profil Pelanggan --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
        {{-- Bagian Atas: Avatar, Nama, Status --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#F3EFEA]">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-[#EDE7DF] border border-[#E5DDD2] flex items-center justify-center font-serif text-xl font-semibold text-[#1C1917] shrink-0 tracking-wide select-none">
                    {{ strtoupper($initials) }}
                </div>
                <div>
                    <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                        {{ $customer->name }}
                    </h2>
                    <p class="text-xs text-[#78716C] mt-0.5">
                        Pelanggan Modeva &middot; ID #{{ $customer->id }}
                    </p>
                </div>
            </div>

            <div>
                <x-admin.status-badge
                    :variant="$customer->is_active ? 'success' : 'danger'"
                    :label="$customer->is_active ? 'Akun Aktif' : 'Akun Nonaktif'"
                />
            </div>
        </div>

        {{-- Detail Informasi Pelanggan --}}
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nama Lengkap</dt>
                <dd class="font-medium text-[#1C1917] mt-1">{{ $customer->name }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Email</dt>
                <dd class="font-medium text-[#1C1917] mt-1">{{ $customer->email }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nomor Telepon</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $customer->customer?->phone ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Tanggal Registrasi</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $customer->created_at?->format('d M Y, H:i') ?? '-' }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Lengkap</dt>
                <dd class="font-medium text-[#1C1917] mt-1 leading-relaxed bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6]">
                    {{ $customer->customer?->address ?? 'Alamat belum dilengkapi.' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Terakhir Diperbarui</dt>
                <dd class="text-xs text-[#78716C] mt-1">
                    {{ $customer->customer?->updated_at?->format('d M Y, H:i') ?? $customer->updated_at?->format('d M Y, H:i') ?? '-' }}
                </dd>
            </div>
        </dl>

        {{-- Aksi Bawah --}}
        <div class="pt-6 border-t border-[#F3EFEA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form
                method="POST"
                action="{{ route('admin.customers.destroy', $customer) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pelanggan {{ addslashes($customer->name) }}? Tindakan ini tidak dapat dibatalkan.');"
            >
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger" icon="trash">
                    Hapus Pelanggan
                </x-admin.button>
            </form>

            <div class="flex items-center gap-3">
                <x-admin.button :href="route('admin.customers.index')" variant="secondary">
                    Kembali ke Daftar
                </x-admin.button>
                <x-admin.button :href="route('admin.customers.edit', $customer)" variant="primary" icon="pencil">
                    Ubah Data
                </x-admin.button>
            </div>
        </div>
    </div>
</div>
@endsection
