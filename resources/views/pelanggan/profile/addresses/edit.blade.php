@extends('layouts.store')

@section('title', 'Ubah Alamat Pengiriman - MODEVA')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-[#E5DDD2]">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#8C5E28]">
                Akun Saya
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight mt-1">
                Ubah Alamat Pengiriman
            </h1>
            <p class="text-sm text-[#78716C] mt-1">
                Perbarui informasi alamat penerima untuk pengiriman pesanan Anda.
            </p>
        </div>

        <a
            href="{{ route('profile.index') }}"
            class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-[#5C3D2E] hover:text-[#1C1917] underline underline-offset-4 transition-colors self-start sm:self-auto"
        >
            <x-icon name="arrow-left" class="w-4 h-4" strokeWidth="1.75" />
            Kembali ke Profil
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form method="POST" action="{{ route('profile.addresses.update', $address) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-admin.form-field
                    name="recipient_name"
                    label="Nama Penerima"
                    :value="$address->recipient_name"
                    required
                />

                <x-admin.form-field
                    name="phone"
                    label="Nomor Telepon"
                    :value="$address->phone"
                    required
                />
            </div>

            <div class="space-y-1.5">
                <label for="address" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                    Alamat Lengkap <span class="text-rose-600">*</span>
                </label>
                <textarea
                    name="address"
                    id="address"
                    rows="3"
                    required
                    placeholder="Nama jalan, nomor rumah, gedung, RT/RW, patokan..."
                    class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('address') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors shadow-2xs"
                >{{ old('address', $address->address) }}</textarea>
                @error('address')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-admin.form-field
                    name="district"
                    label="Kecamatan"
                    :value="$address->district"
                    required
                />

                <x-admin.form-field
                    name="city"
                    label="Kota / Kabupaten"
                    :value="$address->city"
                    required
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-admin.form-field
                    name="province"
                    label="Provinsi"
                    :value="$address->province"
                    required
                />

                <x-admin.form-field
                    name="postal_code"
                    label="Kode Pos"
                    :value="$address->postal_code"
                    required
                />
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="is_primary"
                        value="1"
                        {{ old('is_primary', $address->is_primary) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-[#1C1917] border-[#D0C5B8] focus:ring-[#1C1917]"
                    >
                    <div>
                        <span class="text-sm font-medium text-[#1C1917]">Jadikan Alamat Utama</span>
                        <p class="text-xs text-[#78716C]">Alamat utama akan dipilih secara otomatis saat proses checkout.</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F3EFEA]">
                <a
                    href="{{ route('profile.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#EDE7DF] hover:bg-[#E2DAD0] text-[#1C1917] border border-[#D0C5B8] transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center px-6 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shadow-2xs cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
