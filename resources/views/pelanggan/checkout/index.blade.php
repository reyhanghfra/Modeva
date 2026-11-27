@extends('layouts.store')

@section('title', 'Checkout - MODEVA')

@section('content')
@php
    $hasSavedAddresses = isset($addresses) && $addresses->isNotEmpty();
    $hasProfileAddress = filled($customer?->address);
    $hasAnySavedAddress = $hasSavedAddresses || $hasProfileAddress;
    $defaultAddressOption = $hasSavedAddresses ? 'saved_address' : ($hasProfileAddress ? 'profile' : 'custom');
    $selectedAddressOption = old('address_option', $defaultAddressOption);
    $selectedAddressId = old('selected_address_id', $primaryAddress?->id ?? null);
@endphp

<div class="space-y-8">
    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-[#E5DDD2]">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#8C5E28]">
                Penyelesaian Pesanan
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight mt-1">
                Checkout
            </h1>
            <p class="text-sm text-[#78716C] mt-1">
                Lengkapi informasi penerima, alamat pengiriman, jasa kirim, dan metode pembayaran Anda.
            </p>
        </div>

        <a
            href="{{ route('cart.index') }}"
            class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-[#5C3D2E] hover:text-[#1C1917] underline underline-offset-4 transition-colors self-start sm:self-auto"
        >
            <x-icon name="arrow-left" class="w-4 h-4" strokeWidth="1.75" />
            Kembali ke Keranjang
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

    <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf

        {{-- Kolom Kiri: Formulir Penerima, Alamat, Jasa Pengiriman, & Metode Pembayaran (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Card 1: Data Penerima --}}
            <section class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-5">
                <div class="pb-3 border-b border-[#F3EFEA]">
                    <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                        Data Penerima
                    </h2>
                    <p class="text-xs text-[#78716C] mt-0.5">
                        Informasi kontak penerima paket yang dapat dihubungi kurir
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="recipient_name" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                            Nama Penerima <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="text"
                            name="recipient_name"
                            id="recipient_name"
                            value="{{ old('recipient_name', $primaryAddress?->recipient_name ?? $user->name) }}"
                            required
                            placeholder="Nama lengkap penerima"
                            class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('recipient_name') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                        >
                        @error('recipient_name')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="recipient_phone" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                            Nomor Telepon <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="text"
                            name="recipient_phone"
                            id="recipient_phone"
                            value="{{ old('recipient_phone', $primaryAddress?->phone ?? $customer?->phone) }}"
                            required
                            placeholder="Contoh: 081234567890"
                            class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('recipient_phone') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                        >
                        @error('recipient_phone')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Card 2: Alamat Pengiriman --}}
            <section class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-5">
                <div class="pb-3 border-b border-[#F3EFEA] flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                            Alamat Pengiriman
                        </h2>
                        <p class="text-xs text-[#78716C] mt-0.5">
                            Pilih alamat tersimpan pada profil Anda atau masukkan alamat pengiriman baru
                        </p>
                    </div>

                    <a href="{{ route('profile.index') }}" class="text-xs font-medium text-[#8C5E28] hover:underline shrink-0">
                        Kelola Alamat
                    </a>
                </div>

                @if ($hasSavedAddresses)
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $selectedAddressOption === 'saved_address' ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="address_option"
                                value="saved_address"
                                {{ $selectedAddressOption === 'saved_address' ? 'checked' : '' }}
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="flex-1 space-y-2.5">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    Pilih dari Alamat Tersimpan
                                </span>

                                <div class="space-y-2">
                                    @foreach ($addresses as $addr)
                                        <label class="flex items-start gap-2.5 p-3 rounded-xl border cursor-pointer transition-colors {{ (string) $selectedAddressId === (string) $addr->id ? 'bg-white border-[#8C5E28]' : 'bg-white/70 border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                                            <input
                                                type="radio"
                                                name="selected_address_id"
                                                value="{{ $addr->id }}"
                                                {{ (string) $selectedAddressId === (string) $addr->id ? 'checked' : '' }}
                                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                                            >
                                            <div class="min-w-0 space-y-0.5 text-xs">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-semibold text-[#1C1917]">{{ $addr->recipient_name }}</span>
                                                    <span class="text-[#78716C]">&middot; {{ $addr->phone }}</span>
                                                    @if ($addr->is_primary)
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FAF2E8] text-[#8C5E28] border border-[#ECD9C3]">
                                                            Alamat Utama
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-[#57534E] leading-relaxed">
                                                    {{ $addr->formatted_full_address }}
                                                </p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $selectedAddressOption === 'custom' ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="address_option"
                                value="custom"
                                {{ $selectedAddressOption === 'custom' ? 'checked' : '' }}
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="flex-1 space-y-1">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    Kirim ke Alamat Baru
                                </span>
                                <span class="block text-xs text-[#78716C]">
                                    Isi alamat lengkap tujuan di bawah ini
                                </span>
                            </div>
                        </label>
                    </div>
                @elseif ($hasProfileAddress)
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $selectedAddressOption === 'profile' ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="address_option"
                                value="profile"
                                {{ $selectedAddressOption === 'profile' ? 'checked' : '' }}
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="space-y-1">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    Gunakan Alamat Tersimpan dari Profil
                                </span>
                                <p class="text-xs sm:text-sm text-[#57534E] leading-relaxed">
                                    {{ $customer->address }}
                                </p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $selectedAddressOption === 'custom' ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="address_option"
                                value="custom"
                                {{ $selectedAddressOption === 'custom' ? 'checked' : '' }}
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="flex-1 space-y-1">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    Kirim ke Alamat Baru
                                </span>
                                <span class="block text-xs text-[#78716C]">
                                    Isi alamat lengkap tujuan di bawah ini
                                </span>
                            </div>
                        </label>
                    </div>
                @else
                    <input type="hidden" name="address_option" value="custom">
                @endif

                <div id="customAddressWrapper" class="space-y-1.5">
                    <label for="shipping_address" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
                        Alamat Pengiriman Lengkap
                        @if (! $hasAnySavedAddress)
                            <span class="text-rose-600">*</span>
                        @endif
                    </label>
                    <textarea
                        name="shipping_address"
                        id="shipping_address"
                        rows="3"
                        placeholder="Masukkan nama jalan, nomor rumah, RT/RW, kecamatan, kota, dan kode pos..."
                        class="block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border {{ $errors->has('shipping_address') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#1C1917] focus:ring-[#1C1917]' }} focus:outline-none focus:ring-1 transition-colors"
                    >{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Card 3: Jasa Pengiriman --}}
            <section class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-5">
                <div class="pb-3 border-b border-[#F3EFEA]">
                    <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                        Jasa Pengiriman
                    </h2>
                    <p class="text-xs text-[#78716C] mt-0.5">
                        Pilih layanan kurir yang tersedia untuk pengiriman pesanan Anda
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($shippingServices as $service)
                        @php
                            $isSelected = old('shipping_service', 'JNE Reguler') === $service['code'];
                        @endphp
                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $isSelected ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="shipping_service"
                                value="{{ $service['code'] }}"
                                {{ $isSelected ? 'checked' : '' }}
                                required
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="min-w-0">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    {{ $service['name'] }}
                                </span>
                                <span class="block text-xs text-[#78716C] mt-0.5">
                                    {{ $service['estimate'] }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('shipping_service')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            {{-- Card 4: Metode Pembayaran --}}
            <section class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-5">
                <div class="pb-3 border-b border-[#F3EFEA]">
                    <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                        Metode Pembayaran
                    </h2>
                    <p class="text-xs text-[#78716C] mt-0.5">
                        Transfer bank ke rekening resmi MODEVA (verifikasi manual oleh tim kami)
                    </p>
                </div>

                <input type="hidden" name="payment_method" value="transfer">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($paymentBanks as $bank)
                        @php
                            $isBankSelected = old('bank_name', 'BCA') === $bank['code'];
                        @endphp
                        <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-colors {{ $isBankSelected ? 'bg-[#FAF8F5] border-[#5C3D2E]' : 'bg-white border-[#EFECE6] hover:border-[#D0C5B8]' }}">
                            <input
                                type="radio"
                                name="bank_name"
                                value="{{ $bank['code'] }}"
                                {{ $isBankSelected ? 'checked' : '' }}
                                required
                                class="mt-1 text-[#1C1917] focus:ring-[#1C1917]"
                            >
                            <div class="min-w-0">
                                <span class="block text-sm font-semibold text-[#1C1917]">
                                    Transfer {{ $bank['label'] }}
                                </span>
                                <span class="block text-xs text-[#78716C] mt-0.5">
                                    {{ $bank['account'] }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('bank_name')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Kolom Kanan: Ringkasan Pesanan & Daftar Produk (5 cols) --}}
        <aside class="lg:col-span-5 lg:sticky lg:top-28">
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
                <div class="pb-4 border-b border-[#F3EFEA] flex items-center justify-between">
                    <div>
                        <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                            Ringkasan Pesanan
                        </h2>
                        <p class="text-xs text-[#78716C] mt-0.5">
                            {{ $totalItems }} produk di keranjang Anda
                        </p>
                    </div>
                </div>

                {{-- Daftar Produk --}}
                <div class="divide-y divide-[#F3EFEA] max-h-96 overflow-y-auto pr-1">
                    @foreach ($cartItems as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                            $imageUrl = $item->product_image_url;
                        @endphp
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-start gap-3.5">
                            <div class="w-16 h-20 rounded-xl overflow-hidden bg-[#EDE7DF] border border-[#E5DDD2] shrink-0 flex items-center justify-center">
                                @if ($imageUrl)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $product?->name ?? 'Produk Modeva' }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <span class="font-serif text-sm font-semibold tracking-wider text-[#5C3D2E] uppercase">
                                        {{ mb_substr($product?->name ?? 'M', 0, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-[#1C1917] leading-snug truncate">
                                    {{ $product?->name ?? 'Produk' }}
                                </p>
                                <p class="text-xs text-[#78716C] mt-0.5">
                                    Ukuran: {{ $variant?->size ?? '-' }} &middot; Warna: {{ $variant?->color ?? '-' }}
                                </p>
                                <p class="text-xs text-[#78716C] mt-1">
                                    {{ $item->quantity }} &times; {{ $item->formatted_unit_price }}
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-sm font-semibold text-[#1C1917]">
                                    {{ $item->formatted_subtotal }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Perhitungan Subtotal, Biaya Pengiriman, & Total Pembayaran --}}
                <dl class="space-y-3 pt-4 border-t border-[#F3EFEA] text-sm">
                    <div class="flex items-center justify-between text-[#57534E]">
                        <dt>Subtotal Produk</dt>
                        <dd class="font-semibold text-[#1C1917]">{{ $formattedSubtotal }}</dd>
                    </div>

                    <div class="flex items-center justify-between text-[#57534E]">
                        <dt>Biaya Pengiriman</dt>
                        <dd class="text-xs sm:text-sm font-medium text-[#1C1917]">
                            {{ $formattedShippingCost }}
                            <span class="block text-[11px] text-[#78716C] text-right font-normal">
                                (Tarif ongkir belum diaktifkan)
                            </span>
                        </dd>
                    </div>

                    <div class="pt-4 border-t border-[#F3EFEA] flex items-center justify-between">
                        <dt class="font-semibold text-[#1C1917]">Total Pembayaran</dt>
                        <dd class="font-serif text-2xl font-bold text-[#1C1917]">
                            {{ $formattedTotal }}
                        </dd>
                    </div>
                </dl>

                <div class="pt-2 space-y-3">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center px-6 py-3.5 rounded-full text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shadow-2xs cursor-pointer"
                    >
                        Buat Pesanan
                    </button>

                    <p class="text-[11px] text-center text-[#78716C] leading-relaxed">
                        Dengan menekan <strong>Buat Pesanan</strong>, stok varian akan langsung dialokasikan untuk transaksi Anda.
                    </p>
                </div>
            </div>
        </aside>
    </form>
</div>
@endsection
