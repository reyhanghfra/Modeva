<!DOCTYPE html>
<html lang="id" class="bg-[#FAF8F5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Modeva - Toko Fashion Pilihan')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh flex flex-col text-[#1C1917] font-sans antialiased bg-[#FAF8F5]">
@php
    $home = route('home');
    $catalog = Route::has('katalog.index') ? route('katalog.index') : (Route::has('products.index') ? route('products.index') : '#');
    $track = Route::has('pelanggan.orders.history') ? route('pelanggan.orders.history') : (Route::has('pelanggan.orders.index') ? route('pelanggan.orders.index') : '#');
    $cart = Route::has('cart.index') ? route('cart.index') : '#';
    $navLinks = [
        ['Beranda', $home, request()->routeIs('home')],
        ['Belanja', $catalog, false],
        ['Kategori', $home . '#kategori', false],
        ['Lacak Pesanan', $track, request()->routeIs('pelanggan.orders.*')],
    ];
    $footerCategories = \App\Models\Category::active()->orderBy('name')->pluck('name');
@endphp

    <div class="bg-[#222222] text-[#FAF8F5] text-xs text-center py-2.5 px-4">
        Gratis ongkir untuk pembelian di atas Rp1.500.000 · Pembayaran via transfer bank
    </div>

    <header class="sticky top-0 z-40 bg-[#FAF8F5]/90 backdrop-blur-md border-b border-[#EFECE6]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center">
            <div class="flex-1 flex items-center">
                <button id="menuToggle" type="button" class="md:hidden p-2 -ml-2 rounded-full hover:bg-[#EDE7DF]" aria-label="Menu">
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
                <nav class="hidden md:flex gap-8 text-sm">
                    @foreach ($navLinks as [$name, $url, $active])
                        <a href="{{ $url }}" class="transition-colors {{ $active ? 'text-[#1C1917] font-medium' : 'text-[#78716C] hover:text-[#1C1917]' }}">{{ $name }}</a>
                    @endforeach
                </nav>
            </div>

            <a href="{{ $home }}" class="font-serif text-2xl sm:text-3xl font-semibold tracking-[0.3em]">MODEVA</a>

            <div class="flex-1 flex items-center justify-end gap-2 sm:gap-4 text-sm">
                <a href="{{ $catalog }}" class="p-2 rounded-full hover:bg-[#EDE7DF]" aria-label="Cari produk">
                    <x-icon name="search" class="w-5 h-5" />
                </a>
                <a href="{{ $cart }}" class="p-2 rounded-full hover:bg-[#EDE7DF]" aria-label="Keranjang">
                    <x-icon name="shopping-bag" class="w-5 h-5" />
                </a>
                @auth
                    <a href="{{ route(auth()->user()->homeRoute()) }}" class="hidden sm:inline text-[#78716C] hover:text-[#1C1917]">Akun Saya</a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="text-[#78716C] hover:text-[#1C1917]">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline text-[#78716C] hover:text-[#1C1917]">Masuk</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline px-4 py-2 rounded-full bg-[#222222] text-white hover:bg-black transition-colors">Daftar</a>
                @endauth
            </div>
        </div>

        <div id="mobileMenu" class="hidden md:hidden border-t border-[#EFECE6] px-4 py-4 space-y-1">
            @foreach ($navLinks as [$name, $url, $active])
                <a href="{{ $url }}" class="block px-3 py-2.5 rounded-xl {{ $active ? 'bg-[#EDE7DF] font-medium' : 'text-[#78716C]' }}">{{ $name }}</a>
            @endforeach
            <div class="pt-3 mt-2 border-t border-[#EFECE6] flex gap-2">
                @auth
                    <a href="{{ route(auth()->user()->homeRoute()) }}" class="flex-1 text-center py-2.5 rounded-full border border-[#D0C5B8]">Akun Saya</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-full bg-[#222222] text-white">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex-1 text-center py-2.5 rounded-full border border-[#D0C5B8]">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center py-2.5 rounded-full bg-[#222222] text-white">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-[#222222] text-[#FAF8F5] mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid gap-12 md:grid-cols-[2fr_1fr_1fr]">
            <div>
                <span class="font-serif text-3xl font-semibold tracking-[0.3em]">MODEVA</span>
                <p class="mt-6 max-w-sm text-[#A8A29E] leading-relaxed">Butik fashion independen dengan koleksi pakaian, sepatu, dan tas pilihan untuk gaya harian yang tenang dan berkelas.</p>
            </div>
            <div>
                <h3 class="text-xs uppercase tracking-[0.25em] text-[#A8A29E]">Belanja</h3>
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ($footerCategories as $name)
                        <li><a href="{{ $catalog }}" class="hover:text-white">{{ $name }}</a></li>
                    @endforeach
                    <li><a href="{{ $track }}" class="hover:text-white">Lacak Pesanan</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xs uppercase tracking-[0.25em] text-[#A8A29E]">Kontak</h3>
                <ul class="mt-6 space-y-3 text-sm">
                    <li>Jl. Braga No. 28, Bandung 40111</li>
                    <li>WhatsApp 0812-3000-4488</li>
                    <li>halo@modeva.id</li>
                    <li>Senin–Sabtu, 09.00–18.00 WIB</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-[#33312E]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row justify-between gap-2 text-xs text-[#A8A29E]">
                <span>&copy; {{ date('Y') }} MODEVA. Hak cipta dilindungi.</span>
                <a href="{{ route('login') }}" class="hover:text-white">Masuk Admin / Staf</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('menuToggle')?.addEventListener('click', () =>
            document.getElementById('mobileMenu').classList.toggle('hidden'));
    </script>
    @stack('scripts')
</body>
</html>