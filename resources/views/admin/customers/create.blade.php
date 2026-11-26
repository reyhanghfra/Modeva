@extends('layouts.admin')

@section('title', 'Tambah Pelanggan - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Tambah Pelanggan"
        subtitle="Buat akun pelanggan baru beserta informasi kontak dan alamat pengiriman"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.customers.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-6">
            @csrf

            {{-- Nama Lengkap --}}
            <x-admin.form-field
                name="name"
                label="Nama Lengkap"
                placeholder="Contoh: Anindya Putri"
                required
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Email --}}
                <x-admin.form-field
                    name="email"
                    label="Alamat Email"
                    type="email"
                    placeholder="nama@domain.com"
                    required
                />

                {{-- Nomor Telepon --}}
                <x-admin.form-field
                    name="phone"
                    label="Nomor Telepon"
                    type="text"
                    placeholder="Contoh: 081234567890"
                    required
                />
            </div>

            {{-- Alamat Lengkap --}}
            <div class="space-y-1.5">
                <label for="address" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Alamat Lengkap <span class="text-rose-600">*</span>
                </label>
                <textarea
                    name="address"
                    id="address"
                    rows="3"
                    required
                    placeholder="Masukkan alamat lengkap pelanggan (nama jalan, nomor rumah, kota)..."
                    class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('address') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Password --}}
                <x-admin.form-field
                    name="password"
                    label="Password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    required
                />

                {{-- Konfirmasi Password --}}
                <x-admin.form-field
                    name="password_confirmation"
                    label="Konfirmasi Password"
                    type="password"
                    placeholder="Ulangi password"
                    required
                />
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
                        <span class="text-sm font-medium text-[#1C1917]">Status Akun Aktif</span>
                        <p class="text-xs text-[#78716C]">Pelanggan dengan status aktif dapat masuk dan berbelanja di Modeva.</p>
                    </div>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <x-admin.button :href="route('admin.customers.index')" variant="secondary">
                    Batal
                </x-admin.button>
                <x-admin.button type="submit" variant="primary">
                    Simpan Pelanggan
                </x-admin.button>
            </div>
        </form>
    </div>
</div>
@endsection
