@extends('layouts.admin')

@section('title', 'Data Pelanggan - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Data Pelanggan"
        :subtitle="$totalCustomers . ' pelanggan terdaftar di sistem'"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.customers.create')" icon="plus">
                Tambah Pelanggan
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Tabel Pelanggan --}}
    <x-admin.data-table
        :headers="[
            'Nama',
            'Email',
            'Nomor Telepon',
            'Alamat',
            'Tanggal Registrasi',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
    >
        @forelse ($customers as $customer)
            @php
                $initials = collect(explode(' ', $customer->name))
                    ->map(fn ($part) => mb_substr($part, 0, 1))
                    ->take(2)
                    ->join('');
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors">
                {{-- Nama dengan Avatar Inisial --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#EDE7DF] border border-[#E5DDD2] flex items-center justify-center font-semibold text-xs text-[#1C1917] shrink-0 tracking-wide select-none">
                            {{ strtoupper($initials) }}
                        </div>
                        <div class="min-w-0">
                            <a
                                href="{{ route('admin.customers.show', $customer) }}"
                                class="font-medium text-[#1C1917] hover:underline truncate block"
                            >
                                {{ $customer->name }}
                            </a>
                            <div class="mt-1">
                                <x-admin.status-badge
                                    :variant="$customer->is_active ? 'success' : 'danger'"
                                    :label="$customer->is_active ? 'Aktif' : 'Nonaktif'"
                                />
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Email --}}
                <td class="px-5 sm:px-6 py-4 text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">
                    {{ $customer->email }}
                </td>

                {{-- Nomor Telepon --}}
                <td class="px-5 sm:px-6 py-4 text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">
                    {{ $customer->customer?->phone ?? '-' }}
                </td>

                {{-- Alamat --}}
                <td class="px-5 sm:px-6 py-4 max-w-xs">
                    <p class="text-xs text-[#78716C] line-clamp-2">
                        {{ $customer->customer?->address ?? '-' }}
                    </p>
                </td>

                {{-- Tanggal Registrasi --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#78716C] whitespace-nowrap">
                    {{ $customer->created_at?->format('d M Y') ?? '-' }}
                </td>

                {{-- Tombol Aksi --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        {{-- Tombol Detail --}}
                        <a
                            href="{{ route('admin.customers.show', $customer) }}"
                            class="p-1.5 rounded-lg text-[#78716C] hover:text-[#1C1917] hover:bg-[#EDE7DF] transition-colors"
                            title="Lihat detail pelanggan"
                        >
                            <x-icon name="eye" class="w-4 h-4" strokeWidth="2" />
                        </a>

                        {{-- Tombol Edit --}}
                        <a
                            href="{{ route('admin.customers.edit', $customer) }}"
                            class="p-1.5 rounded-lg text-[#78716C] hover:text-[#1C1917] hover:bg-[#EDE7DF] transition-colors"
                            title="Ubah data pelanggan"
                        >
                            <x-icon name="pencil" class="w-4 h-4" strokeWidth="2" />
                        </a>

                        {{-- Tombol Hapus --}}
                        <form
                            method="POST"
                            action="{{ route('admin.customers.destroy', $customer) }}"
                            class="inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pelanggan {{ addslashes($customer->name) }}? Tindakan ini tidak dapat dibatalkan.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors cursor-pointer"
                                title="Hapus pelanggan"
                            >
                                <x-icon name="trash" class="w-4 h-4" strokeWidth="2" />
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="users" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Belum ada data pelanggan</p>
                        <p class="text-xs text-[#78716C]">Tambahkan pelanggan baru atau tunggu pelanggan mendaftar ke toko.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
