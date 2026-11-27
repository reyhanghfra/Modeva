@extends('layouts.store')

@section('title', 'Profil Saya - MODEVA')

@section('content')
<div class="space-y-8">
    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-[#E5DDD2]">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#8C5E28]">
                Akun Saya
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight mt-1">
                Profil Saya
            </h1>
            <p class="text-sm text-[#78716C] mt-1">
                Kelola informasi akun pribadi dan daftar alamat pengiriman Anda.
            </p>
        </div>

        <a
            href="{{ route('home') }}"
            class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-[#5C3D2E] hover:text-[#1C1917] underline underline-offset-4 transition-colors self-start sm:self-auto"
        >
            <x-icon name="arrow-left" class="w-4 h-4" strokeWidth="1.75" />
            Kembali ke Beranda
        </a>
    </div>

    <x-admin.flash />

    @if ($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-800 space-y-1">
            @foreach ($errors->all() as $error)
                <p class="font-medium">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Bagian 1: Informasi Profil (5 cols) --}}
        <section class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
            <div class="pb-4 border-b border-[#F3EFEA]">
                <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                    Informasi Profil
                </h2>
                <p class="text-xs text-[#78716C] mt-1">
                    Perbarui nama lengkap, alamat email, dan nomor telepon aktif Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label for="name" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                        Nama Lengkap <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        placeholder="Masukkan nama lengkap"
                        class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('name') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                        Alamat Email <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        placeholder="nama@domain.com"
                        class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                    >
                    @error('email')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="phone" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                        Nomor Telepon <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        value="{{ old('phone', $customer?->phone) }}"
                        required
                        placeholder="Contoh: 081234567890"
                        class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('phone') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                    >
                    @error('phone')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shadow-2xs cursor-pointer"
                    >
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </section>

        {{-- Bagian 2: Alamat Pengiriman (7 cols) --}}
        <section class="lg:col-span-7 space-y-5">
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                        Alamat Pengiriman
                    </h2>
                    <p class="text-xs text-[#78716C] mt-1">
                        Kelola alamat tujuan yang akan digunakan saat proses checkout.
                    </p>
                </div>

                <a
                    href="{{ route('profile.addresses.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shrink-0 shadow-2xs"
                >
                    <x-icon name="plus" class="w-4 h-4" strokeWidth="2" />
                    Tambah Alamat
                </a>
            </div>

            @forelse ($addresses as $address)
                <article class="bg-white rounded-3xl p-6 sm:p-7 border {{ $address->is_primary ? 'border-[#8C5E28]/60 ring-1 ring-[#8C5E28]/15' : 'border-[#EFECE6]' }} shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h3 class="text-base font-semibold text-[#1C1917]">
                                    {{ $address->recipient_name }}
                                </h3>
                                @if ($address->is_primary)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-semibold bg-[#FAF2E8] text-[#8C5E28] border border-[#ECD9C3]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#B87A33]"></span>
                                        Alamat Utama
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs sm:text-sm text-[#57534E] font-medium">
                                {{ $address->phone }}
                            </p>
                        </div>
                    </div>

                    <div class="text-sm text-[#1C1917] bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6] space-y-1.5 leading-relaxed">
                        <p>{{ $address->address }}</p>
                        <p class="text-xs text-[#78716C]">
                            Kec. {{ $address->district }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-[#F3EFEA] flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <a
                                href="{{ route('profile.addresses.edit', $address) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-medium bg-[#EDE7DF] hover:bg-[#E2DAD0] text-[#1C1917] border border-[#D0C5B8] transition-colors"
                            >
                                <x-icon name="pencil" class="w-3.5 h-3.5" strokeWidth="2" />
                                Edit
                            </a>

                            <form
                                method="POST"
                                action="{{ route('profile.addresses.destroy', $address) }}"
                                class="inline"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat pengiriman ini?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-medium bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 transition-colors cursor-pointer"
                                >
                                    <x-icon name="trash" class="w-3.5 h-3.5" strokeWidth="2" />
                                    Hapus
                                </button>
                            </form>
                        </div>

                        @if (! $address->is_primary)
                            <form method="POST" action="{{ route('profile.addresses.primary', $address) }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-medium bg-[#4A3428] hover:bg-[#1C1917] text-[#FAF8F5] transition-colors cursor-pointer"
                                >
                                    <x-icon name="check" class="w-3.5 h-3.5" strokeWidth="2" />
                                    Jadikan Alamat Utama
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-3xl p-10 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#EDE7DF] border border-[#E5DDD2] flex items-center justify-center mx-auto text-[#5C3D2E]">
                        <x-icon name="home" class="w-5 h-5" strokeWidth="1.75" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-serif text-xl font-semibold text-[#1C1917]">
                            Belum Ada Alamat Pengiriman Tersimpan
                        </h3>
                        <p class="text-xs sm:text-sm text-[#78716C] max-w-md mx-auto">
                            Tambahkan alamat pengiriman agar proses checkout lebih cepat dan mudah.
                        </p>
                    </div>
                    <div>
                        <a
                            href="{{ route('profile.addresses.create') }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" strokeWidth="2" />
                            Tambah Alamat
                        </a>
                    </div>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection
