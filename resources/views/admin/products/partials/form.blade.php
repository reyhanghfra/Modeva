@php
    $isEditing = $product->exists;
    $variantRows = is_array($variantRows) && count($variantRows) > 0 ? $variantRows : [[]];
@endphp

<div class="rounded-3xl border border-[#EFECE6] bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] sm:p-8">
    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <section class="space-y-5">
            <div>
                <h2 class="font-serif text-2xl text-[#1C1917]">Informasi Produk</h2>
                <p class="mt-1 text-sm text-[#78716C]">Detail koleksi yang akan ditampilkan di katalog.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="name" class="block text-sm font-medium text-[#1C1917]">Nama Produk <span class="text-rose-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255" class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                    @error('name') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="sku" class="block text-sm font-medium text-[#1C1917]">SKU <span class="text-rose-600">*</span></label>
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="255" placeholder="Contoh: MDV-PAK-001" class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 font-mono text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                    @error('sku') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="category_id" class="block text-sm font-medium text-[#1C1917]">Kategori <span class="text-rose-600">*</span></label>
                    <select id="category_id" name="category_id" required class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="brand" class="block text-sm font-medium text-[#1C1917]">Merek <span class="text-rose-600">*</span></label>
                    <input id="brand" name="brand" value="{{ old('brand', $product->brand) }}" required maxlength="255" placeholder="Contoh: Kala Studio" class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                    @error('brand') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="price" class="block text-sm font-medium text-[#1C1917]">Harga (Rp) <span class="text-rose-600">*</span></label>
                    <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $product->price) }}" required class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                    @error('price') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="image" class="block text-sm font-medium text-[#1C1917]">Foto Produk</label>
                    <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-[#E5DDD2] bg-white text-sm text-[#57534E] file:mr-3 file:border-0 file:bg-[#EDE7DF] file:px-4 file:py-3 file:text-xs file:font-medium file:text-[#1C1917]">
                    <p class="text-xs text-[#78716C]">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
                    @error('image') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            @if ($isEditing && $product->image)
                <div class="flex items-center gap-3 rounded-xl bg-[#FAF8F5] p-3">
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-lg object-cover">
                    <p class="text-xs text-[#78716C]">Foto saat ini. Unggah file baru untuk menggantinya.</p>
                </div>
            @endif

            <div class="space-y-1.5">
                <label for="description" class="block text-sm font-medium text-[#1C1917]">Deskripsi <span class="text-rose-600">*</span></label>
                <textarea id="description" name="description" rows="4" required maxlength="5000" class="w-full rounded-xl border border-[#E5DDD2] px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-start gap-3 rounded-xl bg-[#FAF8F5] p-4">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="mt-0.5 h-4 w-4 rounded border-[#D0C5B8] text-[#1F1F1E] focus:ring-[#78716C]">
                <span>
                    <span class="block text-sm font-medium text-[#1C1917]">Produk aktif</span>
                    <span class="mt-1 block text-xs text-[#78716C]">Produk aktif dapat ditampilkan di katalog.</span>
                </span>
            </label>
        </section>

        <section class="space-y-4 border-t border-[#F3EFEA] pt-7" id="variant-editor">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-serif text-2xl text-[#1C1917]">Varian & Stok</h2>
                    <p class="mt-1 text-sm text-[#78716C]">Setiap kombinasi warna dan ukuran memiliki stok tersendiri.</p>
                </div>
                <button type="button" id="add-variant" class="inline-flex items-center justify-center rounded-xl border border-[#D0C5B8] px-4 py-2.5 text-sm font-medium text-[#1C1917] transition-colors hover:bg-[#EDE7DF]">
                    + Tambah Varian
                </button>
            </div>

            @error('variants') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

            <div id="variant-rows" class="space-y-3">
                @foreach ($variantRows as $index => $variant)
                    <div class="variant-row grid grid-cols-1 gap-3 rounded-2xl border border-[#EFECE6] bg-[#FAF8F5]/70 p-4 sm:grid-cols-2 lg:grid-cols-[1.2fr_0.8fr_0.7fr_0.8fr_auto]">
                        @if (!empty($variant['id']))
                            <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
                        @endif
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-[#57534E]">Warna</label>
                            <input name="variants[{{ $index }}][color]" value="{{ old("variants.{$index}.color", $variant['color'] ?? '') }}" required maxlength="255" placeholder="Taupe" class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                            @error("variants.{$index}.color") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-[#57534E]">Ukuran</label>
                            <input name="variants[{{ $index }}][size]" value="{{ old("variants.{$index}.size", $variant['size'] ?? '') }}" required maxlength="255" placeholder="M" class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                            @error("variants.{$index}.size") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-[#57534E]">Stok (pcs)</label>
                            <input name="variants[{{ $index }}][stock]" type="number" min="0" value="{{ old("variants.{$index}.stock", $variant['stock'] ?? 0) }}" required class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                            @error("variants.{$index}.stock") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-[#57534E]">Batas Menipis</label>
                            <input name="variants[{{ $index }}][low_stock_threshold]" type="number" min="0" value="{{ old("variants.{$index}.low_stock_threshold", $variant['low_stock_threshold'] ?? 5) }}" required class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                            @error("variants.{$index}.low_stock_threshold") <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end sm:col-span-2 lg:col-span-1">
                            <button type="button" class="remove-variant w-full rounded-lg px-3 py-2.5 text-xs font-medium text-rose-700 hover:bg-rose-50 sm:w-auto" aria-label="Hapus varian">
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-[#F3EFEA] pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-[#E5DDD2] px-5 py-3 text-sm font-medium text-[#57534E] hover:bg-[#FAF8F5]">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1F1F1E] px-5 py-3 text-sm font-medium text-white hover:bg-[#33312E]">{{ $submitLabel }}</button>
        </div>
    </form>
</div>

<template id="variant-template">
    <div class="variant-row grid grid-cols-1 gap-3 rounded-2xl border border-[#EFECE6] bg-[#FAF8F5]/70 p-4 sm:grid-cols-2 lg:grid-cols-[1.2fr_0.8fr_0.7fr_0.8fr_auto]">
        <div class="space-y-1">
            <label class="block text-xs font-medium text-[#57534E]">Warna</label>
            <input name="variants[__INDEX__][color]" required maxlength="255" placeholder="Taupe" class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="space-y-1">
            <label class="block text-xs font-medium text-[#57534E]">Ukuran</label>
            <input name="variants[__INDEX__][size]" required maxlength="255" placeholder="M" class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="space-y-1">
            <label class="block text-xs font-medium text-[#57534E]">Stok (pcs)</label>
            <input name="variants[__INDEX__][stock]" type="number" min="0" value="0" required class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="space-y-1">
            <label class="block text-xs font-medium text-[#57534E]">Batas Menipis</label>
            <input name="variants[__INDEX__][low_stock_threshold]" type="number" min="0" value="5" required class="w-full rounded-lg border border-[#E5DDD2] bg-white px-3 py-2.5 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
        </div>
        <div class="flex items-end sm:col-span-2 lg:col-span-1">
            <button type="button" class="remove-variant w-full rounded-lg px-3 py-2.5 text-xs font-medium text-rose-700 hover:bg-rose-50 sm:w-auto" aria-label="Hapus varian">Hapus</button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.getElementById('variant-rows');
            const template = document.getElementById('variant-template');
            let nextIndex = {{ count($variantRows) }};

            document.getElementById('add-variant')?.addEventListener('click', () => {
                rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex));
                nextIndex += 1;
            });

            rows?.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.remove-variant');

                if (removeButton) {
                    removeButton.closest('.variant-row').remove();
                }
            });
        });
    </script>
@endpush
