@extends('layouts.admin')

@section('title', 'Pengaturan - Ubah Password - Dasbor Modeva')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header
        title="Pengaturan Akun"
        subtitle="Kelola keamanan dan perbarui kata sandi akun Anda"
    />

    <x-admin.flash />

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
        <div>
            <h2 class="font-serif text-xl font-semibold text-[#1C1917]">Ubah Password</h2>
            <p class="text-xs text-[#78716C] mt-1">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Password Saat Ini --}}
            <div class="space-y-1.5">
                <label for="current_password" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Password Saat Ini <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="current_password"
                        id="current_password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full px-4 py-2.5 pr-11 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('current_password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                    >
                    <button
                        type="button"
                        data-toggle-password="current_password"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#A8A29E] hover:text-[#1C1917] focus:outline-none"
                        aria-label="Tampilkan atau sembunyikan password"
                    >
                        <x-icon name="eye" class="w-4 h-4" strokeWidth="2" />
                    </button>
                </div>
                @error('current_password')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div class="space-y-1.5">
                <label for="password" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Password Baru <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="block w-full px-4 py-2.5 pr-11 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                    >
                    <button
                        type="button"
                        data-toggle-password="password"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#A8A29E] hover:text-[#1C1917] focus:outline-none"
                        aria-label="Tampilkan atau sembunyikan password"
                    >
                        <x-icon name="eye" class="w-4 h-4" strokeWidth="2" />
                    </button>
                </div>
                <p class="text-[11px] text-[#78716C]">Password baru harus berbeda dari password saat ini.</p>
                @error('password')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Konfirmasi Password Baru <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        class="block w-full px-4 py-2.5 pr-11 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('password_confirmation') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                    >
                    <button
                        type="button"
                        data-toggle-password="password_confirmation"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#A8A29E] hover:text-[#1C1917] focus:outline-none"
                        aria-label="Tampilkan atau sembunyikan password"
                    >
                        <x-icon name="eye" class="w-4 h-4" strokeWidth="2" />
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Simpan --}}
            <div class="pt-4 border-t border-[#F3EFEA] flex justify-end">
                <x-admin.button type="submit" variant="primary">
                    Simpan Password Baru
                </x-admin.button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                btn.classList.toggle('text-[#1C1917]', isPassword);
                btn.classList.toggle('text-[#A8A29E]', !isPassword);
            });
        });
    });
</script>
@endpush
@endsection
