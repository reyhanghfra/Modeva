@extends('layouts.admin')

@section('title', 'Produk - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    <x-admin.page-header
        title="Produk"
        :subtitle="$totalProducts . ' produk terdaftar di katalog'"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.products.create')" icon="plus">
                Tambah Produk
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_15rem_auto]">
        <label class="relative block">
            <span class="sr-only">Cari nama produk, SKU, atau merek</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, SKU, atau merek..."
                class="w-full rounded-xl border border-[#E5DDD2] bg-white py-3 pl-4 pr-4 text-sm text-[#1C1917] placeholder-[#A8A29E] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]"
            >
        </label>
        <label>
            <span class="sr-only">Filter kategori</span>
            <select
                name="category_id"
                class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm text-[#1C1917] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]"
            >
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded-xl bg-[#1F1F1E] px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-[#33312E]">
            Cari Produk
        </button>
    </form>

    <div class="hidden overflow-hidden rounded-2xl border border-[#EFECE6] bg-white md:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="border-b border-[#EFECE6] bg-[#F7F4F0] text-[11px] uppercase tracking-[0.12em] text-[#78716C]">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Produk</th>
                        <th class="px-5 py-4 font-semibold">Kategori</th>
                        <th class="px-5 py-4 font-semibold">Merek</th>
                        <th class="px-5 py-4 font-semibold">Harga</th>
                        <th class="px-5 py-4 font-semibold">Total Stok</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EFEA]">
                    @forelse ($products as $product)
                        <tr class="transition-colors hover:bg-[#FAF8F5]/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#EFECE6] bg-[#F7F4F0]">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="font-serif text-lg text-[#A8A29E]">M</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-[#1C1917]">{{ $product->name }}</p>
                                        <p class="mt-1 font-mono text-xs text-[#78716C]">{{ $product->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-[#57534E]">{{ $product->category->name }}</td>
                            <td class="px-5 py-4 text-sm text-[#57534E]">{{ $product->brand }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-[#1C1917]">
                                {{ \App\Support\Rupiah::format((int) $product->price) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-[#1C1917]">{{ $product->total_stock }} pcs</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $product->is_active ? 'bg-emerald-50 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg px-3 py-2 text-xs font-medium text-[#57534E] hover:bg-[#EDE7DF] hover:text-[#1C1917]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center text-sm text-[#78716C]">
                                Belum ada produk yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-3 md:hidden">
        @forelse ($products as $product)
            <article class="rounded-2xl border border-[#EFECE6] bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <div class="flex gap-3">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#EFECE6] bg-[#F7F4F0]">
                        @if ($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="font-serif text-xl text-[#A8A29E]">M</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h2 class="truncate font-medium text-[#1C1917]">{{ $product->name }}</h2>
                                <p class="mt-1 font-mono text-xs text-[#78716C]">{{ $product->sku }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-medium {{ $product->is_active ? 'bg-emerald-50 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-[#78716C]">{{ $product->category->name }} <span aria-hidden="true">·</span> {{ $product->brand }}</p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-[#F3EFEA] pt-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-[#A8A29E]">Harga</p>
                        <p class="mt-1 text-sm font-medium text-[#1C1917]">{{ \App\Support\Rupiah::format((int) $product->price) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-[#A8A29E]">Total Stok</p>
                        <p class="mt-1 text-sm font-medium text-[#1C1917]">{{ $product->total_stock }} pcs</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2 border-t border-[#F3EFEA] pt-3">
                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg px-3 py-2 text-xs font-medium text-[#57534E] hover:bg-[#EDE7DF]">Edit</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg px-3 py-2 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[#D0C5B8] bg-white px-5 py-10 text-center text-sm text-[#78716C]">
                Belum ada produk yang cocok dengan pencarian.
            </div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>
</div>
@endsection
