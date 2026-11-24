@extends('layouts.admin')

@section('title', 'Kategori Produk - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Kategori Produk"
        :subtitle="$totalCategories . ' kategori terdaftar di katalog'"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.categories.create')" icon="plus">
                Tambah Kategori
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Pencarian --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <x-admin.search-input placeholder="Cari nama kategori..." />

        <div class="flex items-center gap-3">
            <span class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Status:</span>
            <x-admin.filter-pills
                name="status"
                :options="['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']"
            />
        </div>
    </div>

    {{-- Tabel Kategori --}}
    <x-admin.data-table
        :headers="[
            'Kategori & Slug',
            'Deskripsi',
            'Jumlah Produk',
            'Status',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
        :paginator="$categories"
    >
        @forelse ($categories as $category)
            @php
                // TODO: Hitung produk nyata saat modul Produk (Orang 2) sudah tersedia
                $productsCount = 0;
                if (class_exists(\App\Models\Product::class) && method_exists($category, 'products') && $category->products()) {
                    $productsCount = $category->products()->count();
                }
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors">
                {{-- Nama & Slug --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="font-medium text-[#1C1917]">
                        {{ $category->name }}
                    </div>
                    <div class="text-xs text-[#78716C] font-mono mt-0.5">
                        /{{ $category->slug }}
                    </div>
                </td>

                {{-- Deskripsi --}}
                <td class="px-5 sm:px-6 py-4 max-w-xs sm:max-w-md">
                    <p class="text-xs text-[#78716C] line-clamp-2">
                        {{ $category->description ?: '-' }}
                    </p>
                </td>

                {{-- Jumlah Produk --}}
                <td class="px-5 sm:px-6 py-4 text-xs font-medium text-[#1C1917] whitespace-nowrap">
                    {{ $productsCount }} produk
                </td>

                {{-- Status --}}
                <td class="px-5 sm:px-6 py-4">
                    <x-admin.status-badge
                        :variant="$category->is_active ? 'success' : 'danger'"
                        :label="$category->is_active ? 'Aktif' : 'Nonaktif'"
                    />
                </td>

                {{-- Aksi --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        <a
                            href="{{ route('admin.categories.edit', $category) }}"
                            class="p-1.5 rounded-lg text-[#78716C] hover:text-[#1C1917] hover:bg-[#EDE7DF] transition-colors"
                            title="Ubah kategori"
                        >
                            <x-icon name="pencil" class="w-4 h-4" strokeWidth="2" />
                        </a>

                        <form
                            method="POST"
                            action="{{ route('admin.categories.destroy', $category) }}"
                            class="inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ addslashes($category->name) }}?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors"
                                title="Hapus kategori"
                            >
                                <x-icon name="trash" class="w-4 h-4" strokeWidth="2" />
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="tag" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Tidak ada kategori ditemukan</p>
                        <p class="text-xs text-[#78716C]">Mulai tambahkan kategori produk baru untuk katalog toko.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
