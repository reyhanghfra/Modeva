<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Modeva</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FAF8F5] text-[#1C1917]">
    <main class="max-w-5xl mx-auto px-5 py-10">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E7E0D7] pb-5">
            <a href="{{ route('home') }}" class="font-serif text-3xl tracking-[0.2em]">MODEVA</a>
            <nav class="flex items-center gap-4 text-sm">
                @auth
                    @if(auth()->user()->role === 'pelanggan')
                        <a href="{{ route('pelanggan.orders.history') }}" class="underline underline-offset-4">Riwayat Pesanan</a>
                    @endif
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="underline">Keluar</button></form>
                @else
                    <a href="{{ route('login') }}" class="underline underline-offset-4">Masuk</a>
                @endauth
            </nav>
        </header>
        <section class="py-20 text-center">
            <p class="uppercase tracking-[0.3em] text-xs text-stone-500">Fashion Store</p>
            <h1 class="font-serif text-5xl mt-4">Selamat Datang di MODEVA</h1>
            <p class="text-stone-500 mt-4">Temukan koleksi pilihanmu dan pantau pesanan dengan mudah.</p>
            @auth
                @if(auth()->user()->role === 'pelanggan')
                    <a href="{{ route('pelanggan.orders.history') }}" class="inline-block mt-8 bg-stone-900 text-white rounded-lg px-6 py-3">Lihat Riwayat Pesanan</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="inline-block mt-8 bg-stone-900 text-white rounded-lg px-6 py-3">Masuk ke Akun</a>
            @endauth
        </section>
    </main>
</body>
</html>
