@extends('layouts.admin')

@section('title', 'Tambah Pengguna - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Tambah Pengguna"
        subtitle="Buat akun pengguna baru dengan peran dan kredensial login"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.users.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            {{-- Nama Lengkap --}}
            <x-admin.form-field
                name="name"
                label="Nama Lengkap"
                placeholder="Contoh: Budi Santoso"
                required
            />

            {{-- Email --}}
            <x-admin.form-field
                name="email"
                label="Alamat Email"
                type="email"
                placeholder="nama@domain.com"
                required
            />

            {{-- Peran / Role --}}
            <div class="space-y-1.5">
                <label for="role" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Peran (Role) <span class="text-rose-600">*</span>
                </label>
                <select
                    name="role"
                    id="role"
                    required
                    class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] rounded-xl border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none transition-colors shadow-2xs"
                >
                    <option value="pelanggan" {{ old('role', 'pelanggan') === 'pelanggan' ? 'selected' : '' }}>Pelanggan (Halaman Publik Toko)</option>
                    <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas (Pengelola Pesanan & Stok)</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Akses Penuh Seluruh Sistem)</option>
                </select>
                @error('role')
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
                        <p class="text-xs text-[#78716C]">Akun yang aktif dapat melakukan login ke dalam aplikasi.</p>
                    </div>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <x-admin.button :href="route('admin.users.index')" variant="secondary">
                    Batal
                </x-admin.button>
                <x-admin.button type="submit" variant="primary">
                    Simpan Pengguna
                </x-admin.button>
            </div>
        </form>
    </div>
</div>
@endsection
