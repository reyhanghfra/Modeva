@extends('layouts.admin')

@section('title', 'Inventori - Dasbor Modeva')

@section('content')
@php
    $statusStyles = [
        'Aman' => 'bg-emerald-50 text-emerald-800 ring-emerald-700/10',
        'Menipis' => 'bg-amber-50 text-amber-800 ring-amber-700/10',
        'Habis' => 'bg-rose-50 text-rose-800 ring-rose-700/10',
    ];
    $statusFilters = [
        '' => 'Semua',
        'aman' => 'Aman',
        'menipis' => 'Menipis',
        'habis' => 'Habis',
    ];
@endphp

<div class="space-y-6">
    <x-admin.page-header title="Inventori" subtitle="Pantau ketersediaan stok setiap kombinasi warna dan ukuran.">
        <x-slot:action>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-[#D0C5B8] px-4 py-2.5 text-sm font-medium text-[#1C1917] transition-colors hover:bg-[#EDE7DF]">
                Kelola Produk
            </a>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />
    @if ($errors->has('quantity') || $errors->has('low_stock_threshold') || $errors->has('operation'))
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ $errors->first('quantity') ?: $errors->first('low_stock_threshold') ?: $errors->first('operation') }}
        </div>
    @endif

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <article class="rounded-2xl border border-[#EFECE6] bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#78716C]">Total Unit</p>
            <p class="mt-3 font-serif text-3xl text-[#1C1917]">{{ number_format((int) $summary->total_units, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-[#A8A29E]">pcs tersedia dari seluruh varian</p>
        </article>
        <article class="rounded-2xl border border-amber-100 bg-[#FFFCF5] p-5">
            <p class="text-xs font-medium uppercase tracking-[0.12em] text-amber-800">Menipis</p>
            <p class="mt-3 font-serif text-3xl text-amber-900">{{ number_format((int) $summary->low_stock_count, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-amber-800/70">varian melewati batas stok minimum</p>
        </article>
        <article class="rounded-2xl border border-rose-100 bg-[#FFFAFA] p-5">
            <p class="text-xs font-medium uppercase tracking-[0.12em] text-rose-800">Habis</p>
            <p class="mt-3 font-serif text-3xl text-rose-900">{{ number_format((int) $summary->out_of_stock_count, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-rose-800/70">varian dengan stok nol</p>
        </article>
    </section>

    <form method="GET" action="{{ route('admin.inventory.index') }}" class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="block flex-1">
                <span class="sr-only">Cari nama atau SKU produk</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama atau SKU produk..."
                    class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm placeholder-[#A8A29E] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]"
                >
            </label>
            <button type="submit" class="rounded-xl bg-[#1F1F1E] px-5 py-3 text-sm font-medium text-white hover:bg-[#33312E]">Cari Inventori</button>
        </div>
        <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Filter status stok">
            @foreach ($statusFilters as $filter => $label)
                @php
                    $isSelected = $status === $filter;
                    $query = array_filter(['search' => request('search'), 'status' => $filter], fn ($value) => $value !== '');
                @endphp
                <a
                    href="{{ route('admin.inventory.index', $query) }}"
                    @if ($isSelected) aria-current="page" @endif
                    class="shrink-0 rounded-full border px-4 py-2 text-xs font-medium transition-colors {{ $isSelected ? 'border-[#1F1F1E] bg-[#1F1F1E] text-white' : 'border-[#E5DDD2] bg-white text-[#57534E] hover:bg-[#EDE7DF]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </form>

    <div class="hidden overflow-hidden rounded-2xl border border-[#EFECE6] bg-white md:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="border-b border-[#EFECE6] bg-[#F7F4F0] text-[11px] uppercase tracking-[0.12em] text-[#78716C]">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Produk</th>
                        <th class="px-5 py-4 font-semibold">Varian</th>
                        <th class="px-5 py-4 font-semibold">Stok</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EFEA]">
                    @forelse ($variants as $variant)
                        @php($stockStatus = $variant->stockStatus())
                        <tr class="hover:bg-[#FAF8F5]/70">
                            <td class="px-5 py-4">
                                <p class="font-medium text-[#1C1917]">{{ $variant->product->name }}</p>
                                <p class="mt-1 font-mono text-xs text-[#78716C]">{{ $variant->product->sku }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-[#57534E]">{{ $variant->color }} <span class="text-[#A8A29E]">·</span> {{ $variant->size }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-[#1C1917]">{{ $variant->stock }} pcs</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusStyles[$stockStatus] }}">{{ $stockStatus }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    class="open-stock-modal rounded-lg border border-[#D0C5B8] px-3 py-2 text-xs font-medium text-[#1C1917] hover:bg-[#EDE7DF]"
                                    data-action="{{ route('admin.inventory.stock.update', $variant) }}"
                                    data-product="{{ $variant->product->name }}"
                                    data-variant="{{ $variant->color }} · {{ $variant->size }}"
                                    data-stock="{{ $variant->stock }}"
                                    data-threshold="{{ $variant->low_stock_threshold }}"
                                >
                                    Atur Stok
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#78716C]">Tidak ada varian yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-3 md:hidden">
        @forelse ($variants as $variant)
            @php($stockStatus = $variant->stockStatus())
            <article class="rounded-2xl border border-[#EFECE6] bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate font-medium text-[#1C1917]">{{ $variant->product->name }}</h2>
                        <p class="mt-1 font-mono text-xs text-[#78716C]">{{ $variant->product->sku }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-medium ring-1 ring-inset {{ $statusStyles[$stockStatus] }}">{{ $stockStatus }}</span>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-[#F3EFEA] pt-3">
                    <div>
                        <p class="text-xs text-[#78716C]">{{ $variant->color }} · {{ $variant->size }}</p>
                        <p class="mt-1 text-sm font-semibold text-[#1C1917]">{{ $variant->stock }} pcs</p>
                    </div>
                    <button
                        type="button"
                        class="open-stock-modal rounded-lg border border-[#D0C5B8] px-3 py-2 text-xs font-medium text-[#1C1917] hover:bg-[#EDE7DF]"
                        data-action="{{ route('admin.inventory.stock.update', $variant) }}"
                        data-product="{{ $variant->product->name }}"
                        data-variant="{{ $variant->color }} · {{ $variant->size }}"
                        data-stock="{{ $variant->stock }}"
                        data-threshold="{{ $variant->low_stock_threshold }}"
                    >
                        Atur Stok
                    </button>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[#D0C5B8] bg-white px-5 py-10 text-center text-sm text-[#78716C]">
                Tidak ada varian yang cocok dengan filter.
            </div>
        @endforelse
    </div>

    <div>{{ $variants->links() }}</div>
</div>

<dialog id="stock-modal" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl border border-[#EFECE6] bg-white p-0 text-[#1C1917] shadow-2xl backdrop:bg-[#1C1917]/50">
    <form id="stock-form" method="POST" class="space-y-5 p-5 sm:p-7">
        @csrf
        @method('PATCH')
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.12em] text-[#A8A29E]">Perbarui inventori</p>
                <h2 id="modal-product" class="mt-2 font-serif text-2xl"></h2>
                <p id="modal-variant" class="mt-1 text-sm text-[#78716C]"></p>
            </div>
            <button type="button" id="close-stock-modal" class="rounded-lg px-2 py-1 text-xl leading-none text-[#78716C] hover:bg-[#F7F4F0]" aria-label="Tutup">×</button>
        </div>

        <div class="rounded-xl bg-[#FAF8F5] px-4 py-3 text-sm text-[#57534E]">
            Stok saat ini: <strong id="modal-current-stock" class="text-[#1C1917]"></strong> pcs
        </div>

        <div class="space-y-1.5">
            <label for="stock-operation" class="block text-sm font-medium">Cara perubahan</label>
            <select id="stock-operation" name="operation" required class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                <option value="set">Atur menjadi jumlah ini</option>
                <option value="add">Tambahkan ke stok</option>
                <option value="subtract">Kurangi dari stok</option>
            </select>
        </div>
        <div class="space-y-1.5">
            <label for="stock-quantity" id="stock-quantity-label" class="block text-sm font-medium">Jumlah stok</label>
            <input id="stock-quantity" name="quantity" type="number" min="0" max="4294967295" required class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="space-y-1.5">
            <label for="stock-threshold" class="block text-sm font-medium">Batas stok menipis</label>
            <input id="stock-threshold" name="low_stock_threshold" type="number" min="0" max="4294967295" required class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
            <button type="button" id="cancel-stock-modal" class="rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm font-medium text-[#57534E] hover:bg-[#FAF8F5]">Batal</button>
            <button type="submit" class="rounded-xl bg-[#1F1F1E] px-4 py-3 text-sm font-medium text-white hover:bg-[#33312E]">Simpan Stok</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('stock-modal');
            const form = document.getElementById('stock-form');
            const operation = document.getElementById('stock-operation');
            const quantity = document.getElementById('stock-quantity');
            const quantityLabel = document.getElementById('stock-quantity-label');
            const closeModal = () => modal.close();

            document.querySelectorAll('.open-stock-modal').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = button.dataset.action;
                    document.getElementById('modal-product').textContent = button.dataset.product;
                    document.getElementById('modal-variant').textContent = button.dataset.variant;
                    document.getElementById('modal-current-stock').textContent = button.dataset.stock;
                    quantity.value = button.dataset.stock;
                    document.getElementById('stock-threshold').value = button.dataset.threshold;
                    operation.value = 'set';
                    quantityLabel.textContent = 'Jumlah stok';
                    quantity.max = '4294967295';
                    modal.showModal();
                });
            });

            operation.addEventListener('change', () => {
                const labels = {
                    set: 'Atur stok menjadi',
                    add: 'Jumlah yang ditambahkan',
                    subtract: 'Jumlah yang dikurangi',
                };
                quantityLabel.textContent = labels[operation.value];
                quantity.value = '';
            });

            document.getElementById('close-stock-modal').addEventListener('click', closeModal);
            document.getElementById('cancel-stock-modal').addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });
        });
    </script>
@endpush
