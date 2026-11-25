@extends('layouts.admin')

@section('title', 'Ubah Pelanggan - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Ubah Pelanggan"
        :subtitle="'Memperbarui data pelanggan untuk ' . $customer->name"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.customers.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nama Lengkap --}}
            <x-admin.form-field
                name="name"
                label="Nama Lengkap"
                :value="$customer->name"
                required
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Email --}}
                <x-admin.form-field
                    name="email"
                    label="Alamat Email"
                    type="email"
                    :value="$customer->email"
                    required
                />

                {{-- Nomor Telepon --}}
                <x-admin.form-field
                    name="phone"
                    label="Nomor Telepon"
                    type="text"
                    :value="$customer->customer?->phone"
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
                    placeholder="Masukkan alamat lengkap pelanggan..."
                    class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('address') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                >{{ old('address', $customer->customer?->address) }}</textarea>
                @error('address')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Section Password Baru (Opsional) --}}
            <div class="pt-4 border-t border-[#F3EFEA] space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-[#1C1917]">Ubah Password (Opsional)</h3>
                    <p class="text-xs text-[#78716C] mt-0.5">Biarkan kosong jika tidak ingin mengubah password pelanggan ini.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-admin.form-field
                        name="password"
                        label="Password Baru"
                        type="password"
                        placeholder="Minimal 8 karakter"
                    />

                    <x-admin.form-field
                        name="password_confirmation"
                        label="Ulangi Password Baru"
                        type="password"
                        placeholder="Konfirmasi password baru"
                    />
                </div>
            </div>

            {{-- Status Aktif Toggle --}}
            <div class="pt-4 border-t border-[#F3EFEA]">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-[#222] border-[#D0C5B8] focus:ring-[#222]"
                    >
                    <div>
                        <span class="text-sm font-medium text-[#1C1917]">Status Akun Aktif</span>
                        <p class="text-xs text-[#78716C]">Menonaktifkan akun akan mencegah pelanggan masuk ke sistem.</p>
                    </div>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <x-admin.button :href="route('admin.customers.index')" variant="secondary">
                    Batal
                </x-admin.button>
                <x-admin.button type="submit" variant="primary">
                    Simpan Perubahan
                </x-admin.button>
            </div>
        </form>
    </div>
</div>
@endsection
