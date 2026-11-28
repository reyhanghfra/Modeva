<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF8F5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Koleksi fashion pilihan MODEVA dengan desain timeless dan palet warna natural.')">
    <title>@yield('title', 'MODEVA - Fashion Store')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#FAF8F5] font-sans text-[#1C1917] antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-[#E7E0D7] bg-[#FAF8F5]/95">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="font-serif text-2xl font-semibold tracking-[0.22em] sm:text-3xl" aria-label="MODEVA beranda">
                    MODEVA
                </a>
                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-[#57534E]" aria-label="Navigasi utama">
                    <a href="{{ route('products.index') }}" class="transition-colors hover:text-[#1C1917]">Katalog</a>
                    @auth
                        @if (auth()->user()->role === 'pelanggan' && \Illuminate\Support\Facades\Route::has('pelanggan.orders.history'))
                            <a href="{{ route('pelanggan.orders.history') }}" class="transition-colors hover:text-[#1C1917]">Pesanan</a>
                        @endif
                        <span class="hidden text-[#A8A29E] sm:inline">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="transition-colors hover:text-[#1C1917]">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="transition-colors hover:text-[#1C1917]">Masuk</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="w-full flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-[#E7E0D7]">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-[#78716C] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <span class="font-serif text-sm tracking-[0.18em] text-[#57534E]">MODEVA</span>
                <span>&copy; {{ date('Y') }} Modeva Fashion Store</span>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
