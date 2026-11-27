<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF8F5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MODEVA — Fashion Anak & Dewasa')</title>

    {{-- Google Fonts: Cormorant Garamond & Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-[#FAF8F5] text-[#1C1917] font-sans antialiased">
    @php
        $cartCount = 0;
        if (auth()->check() && auth()->user()->role === 'pelanggan') {
            $cartCount = (int) auth()->user()->cartItems()->sum('quantity');
        }

        $shopUrl = Route::has('catalog.index') ? route('catalog.index') : route('home');
        $categoryUrl = Route::has('categories.index') ? route('categories.index') : route('home');
        $trackOrderUrl = Route::has('orders.track') ? route('orders.track') : route('home');
    @endphp

    {{-- Storefront Top Navbar --}}
    <header class="sticky top-0 z-40 bg-[#FAF8F5]/95 backdrop-blur-md border-b border-[#E5DDD2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
            <div class="h-20 grid grid-cols-3 items-center gap-4">
                {{-- Left: Desktop Navigation Links / Mobile Hamburger --}}
                <div class="flex items-center">
                    <button
                        type="button"
                        id="storeMobileToggle"
                        class="lg:hidden p-2 -ml-2 rounded-xl text-[#1C1917] hover:bg-[#EDE7DF] transition-colors focus:outline-none"
                        aria-label="Buka Menu Navigasi"
                        aria-expanded="false"
                    >
                        <x-icon name="menu" class="w-6 h-6" />
                    </button>

                    <nav class="hidden lg:flex items-center gap-6 text-xs font-medium tracking-wider uppercase text-[#57534E]" aria-label="Navigasi Pelanggan">
                        <a href="{{ route('home') }}" class="hover:text-[#1C1917] transition-colors {{ request()->routeIs('home') ? 'text-[#1C1917] font-semibold' : '' }}">
                            Beranda
                        </a>
                        <a href="{{ $shopUrl }}" class="hover:text-[#1C1917] transition-colors">
                            Belanja
                        </a>
                        <a href="{{ $categoryUrl }}" class="hover:text-[#1C1917] transition-colors">
                            Kategori
                        </a>
                        <a href="{{ $trackOrderUrl }}" class="hover:text-[#1C1917] transition-colors">
                            Lacak Pesanan
                        </a>
                    </nav>
                </div>

                {{-- Center: Brand Logo MODEVA --}}
                <div class="flex justify-center">
                    <a href="{{ route('home') }}" class="font-serif text-2xl sm:text-3xl tracking-[0.28em] font-semibold text-[#1C1917] uppercase select-none">
                        MODEVA
                    </a>
                </div>

                {{-- Right: Cart Icon + Account Actions --}}
                <div class="flex items-center justify-end gap-3 sm:gap-4">
                    <a
                        href="{{ route('cart.index') }}"
                        class="relative inline-flex items-center gap-2 px-3 py-2 rounded-full border {{ request()->routeIs('cart.*') ? 'bg-[#1C1917] text-[#FAF8F5] border-[#1C1917]' : 'bg-white text-[#1C1917] border-[#E5DDD2] hover:bg-[#EDE7DF]' }} transition-colors"
                        aria-label="Keranjang Belanja"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                            <path d="M3 6h18" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                        <span class="text-xs font-semibold">{{ $cartCount }}</span>
                    </a>

                    @auth
                        <div class="hidden sm:flex items-center gap-3">
                            <a
                                href="{{ route('profile.index') }}"
                                class="text-xs font-medium truncate max-w-[140px] transition-colors {{ request()->routeIs('profile.*') ? 'text-[#8C5E28] font-semibold underline underline-offset-4' : 'text-[#57534E] hover:text-[#1C1917]' }}"
                                title="Akun Saya / Profil"
                            >
                                {{ auth()->user()->name }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-xs font-medium text-[#78716C] hover:text-[#1C1917] underline underline-offset-4 transition-colors cursor-pointer"
                                >
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="text-xs font-medium uppercase tracking-wider text-[#1C1917] hover:text-[#8C5E28] transition-colors"
                        >
                            Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div id="storeMobileMenu" class="hidden lg:hidden border-t border-[#E5DDD2] bg-[#FAF8F5]">
            <nav class="px-4 py-4 space-y-2 text-sm font-medium text-[#1C1917]" aria-label="Navigasi Mobile">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-xl hover:bg-[#EDE7DF] transition-colors">Beranda</a>
                <a href="{{ $shopUrl }}" class="block px-3 py-2 rounded-xl hover:bg-[#EDE7DF] transition-colors">Belanja</a>
                <a href="{{ $categoryUrl }}" class="block px-3 py-2 rounded-xl hover:bg-[#EDE7DF] transition-colors">Kategori</a>
                <a href="{{ $trackOrderUrl }}" class="block px-3 py-2 rounded-xl hover:bg-[#EDE7DF] transition-colors">Lacak Pesanan</a>
                @auth
                    <div class="pt-2 mt-2 border-t border-[#E5DDD2] flex items-center justify-between px-3 py-1">
                        <a href="{{ route('profile.index') }}" class="text-xs font-medium text-[#1C1917] hover:underline">
                            Profil Saya ({{ auth()->user()->name }})
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-[#5C3D2E] hover:underline cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Main Storefront Content --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-8 sm:py-12">
        @yield('content')
    </main>

    {{-- Dark Modeva Footer --}}
    <footer class="bg-[#1C1917] text-[#FAF8F5] border-t border-[#2C2B29]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-10 border-b border-[#2F2C28]">
                <div class="space-y-3">
                    <h2 class="font-serif text-2xl tracking-[0.25em] font-semibold uppercase text-[#FAF8F5]">
                        MODEVA
                    </h2>
                    <p class="text-xs sm:text-sm text-[#A8A29E] leading-relaxed max-w-sm">
                        Butik fashion anak-anak dan dewasa dengan siluet minimalis, material pilihan yang nyaman, dan palet warna abadi untuk keseharian Anda.
                    </p>
                </div>

                <div class="space-y-2.5 text-xs sm:text-sm">
                    <h3 class="font-semibold uppercase tracking-wider text-[#D0C5B8] text-xs">Navigasi</h3>
                    <ul class="space-y-2 text-[#A8A29E]">
                        <li><a href="{{ route('home') }}" class="hover:text-[#FAF8F5] transition-colors">Beranda</a></li>
                        <li><a href="{{ $shopUrl }}" class="hover:text-[#FAF8F5] transition-colors">Belanja Koleksi</a></li>
                        <li><a href="{{ $categoryUrl }}" class="hover:text-[#FAF8F5] transition-colors">Kategori Fashion</a></li>
                        <li><a href="{{ $trackOrderUrl }}" class="hover:text-[#FAF8F5] transition-colors">Lacak Pesanan</a></li>
                    </ul>
                </div>

                <div class="space-y-2.5 text-xs sm:text-sm">
                    <h3 class="font-semibold uppercase tracking-wider text-[#D0C5B8] text-xs">Kontak &amp; Layanan Pelanggan</h3>
                    <ul class="space-y-2 text-[#A8A29E]">
                        <li>Email: <span class="text-[#FAF8F5]">halo@modeva.id</span></li>
                        <li>WhatsApp: <span class="text-[#FAF8F5]">+62 812-3456-7890</span></li>
                        <li>Alamat: <span class="text-[#FAF8F5]">Jl. Kemang Raya No. 18, Jakarta Selatan</span></li>
                        <li>Jam Operasional: <span class="text-[#FAF8F5]">Senin - Sabtu, 09.00 - 18.00 WIB</span></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#A8A29E]">
                <p>&copy; {{ date('Y') }} Modeva Fashion Store. Hak cipta dilindungi.</p>
                <p class="tracking-wide">Fashion Anak-Anak &amp; Dewasa</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('storeMobileToggle');
            const menu = document.getElementById('storeMobileMenu');
            if (toggle && menu) {
                toggle.addEventListener('click', () => {
                    const isHidden = menu.classList.toggle('hidden');
                    toggle.setAttribute('aria-expanded', String(!isHidden));
                });
            }
        });
    </script>
</body>
</html>
