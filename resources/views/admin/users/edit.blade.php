@extends('layouts.admin')

@section('title', 'Ubah Pengguna - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Ubah Pengguna"
        :subtitle="'Memperbarui data dan hak akses untuk ' . $user->name"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.users.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nama Lengkap --}}
            <x-admin.form-field
                name="name"
                label="Nama Lengkap"
                :value="$user->name"
                required
            />

            {{-- Email --}}
            <x-admin.form-field
                name="email"
                label="Alamat Email"
                type="email"
                :value="$user->email"
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
                    <option value="pelanggan" {{ old('role', $user->role) === 'pelanggan' ? 'selected' : '' }}>Pelanggan (Halaman Publik Toko)</option>
                    <option value="petugas" {{ old('role', $user->role) === 'petugas' ? 'selected' : '' }}>Petugas (Pengelola Pesanan & Stok)</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Akses Penuh Seluruh Sistem)</option>
                </select>
                @if (auth()->id() === $user->id)
                    <p class="text-xs text-[#78716C]">Catatan: Anda tidak dapat menurunkan peran akun Anda sendiri.</p>
                @endif
                @error('role')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Section Password Baru (Opsional) --}}
            <div class="pt-4 border-t border-[#F3EFEA] space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-[#1C1917]">Ubah Password (Opsional)</h3>
                    <p class="text-xs text-[#78716C] mt-0.5">Biarkan kosong jika tidak ingin mengubah password pengguna ini.</p>
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
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-[#222] border-[#D0C5B8] focus:ring-[#222]"
                    >
                    <div>
                        <span class="text-sm font-medium text-[#1C1917]">Status Akun Aktif</span>
                        <p class="text-xs text-[#78716C]">
                            @if (auth()->id() === $user->id)
                                Anda tidak dapat menonaktifkan akun yang sedang aktif digunakan.
                            @else
                                Menonaktifkan akun akan mencegah pengguna masuk ke sistem.
                            @endif
                        </p>
                    </div>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <x-admin.button :href="route('admin.users.index')" variant="secondary">
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
