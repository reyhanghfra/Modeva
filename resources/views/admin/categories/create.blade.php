@extends('layouts.admin')

@section('title', 'Tambah Kategori - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Tambah Kategori"
        subtitle="Buat kategori baru untuk mengelompokkan koleksi busana dan aksesoris"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.categories.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-6">
            @csrf

            {{-- Nama Kategori --}}
            <x-admin.form-field
                name="name"
                label="Nama Kategori"
                placeholder="Contoh: Pakaian Wanita"
                helper="Slug URL akan dibuat secara otomatis dari nama kategori."
                required
            />

            {{-- Deskripsi --}}
            <div class="space-y-1.5">
                <label for="description" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Deskripsi Kategori (Opsional)
                </label>
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    placeholder="Deskripsi singkat mengenai koleksi dalam kategori ini..."
                    class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('description') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Aktif Toggle --}}
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-[#222] border-[#D0C5B8] focus:ring-[#222]"
                    >
                    <div>
                        <span class="text-sm font-medium text-[#1C1917]">Status Kategori Aktif</span>
                        <p class="text-xs text-[#78716C]">Kategori yang aktif dapat dipilih saat mengelola produk dan tampil di katalog publik.</p>
                    </div>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <x-admin.button :href="route('admin.categories.index')" variant="secondary">
                    Batal
                </x-admin.button>
                <x-admin.button type="submit" variant="primary">
                    Simpan Kategori
                </x-admin.button>
            </div>
        </form>
    </div>
</div>
@endsection
