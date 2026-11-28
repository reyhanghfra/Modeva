<!DOCTYPE html>
<html lang="id" class="bg-[#FAF8F5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor Petugas - Modeva')</title>

    {{-- Google Fonts: Cormorant Garamond & Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen min-h-dvh text-[#1C1917] font-sans antialiased bg-[#FAF8F5]">
    <div class="min-h-screen min-h-dvh">
        {{-- Mobile Backdrop Overlay --}}
        <div
            id="adminSidebarBackdrop"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar Component --}}
        <x-petugas.sidebar />

        {{-- Mobile Top Navigation Bar --}}
        <header class="sticky top-0 z-30 flex items-center justify-between px-4 py-3 bg-[#FAF8F5]/90 backdrop-blur-md border-b border-[#EFECE6] lg:hidden">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    id="adminMobileToggle"
                    class="p-2 rounded-xl text-[#1C1917] hover:bg-[#EDE7DF] focus:outline-none transition-colors"
                    aria-label="Buka Menu Navigasi"
                >
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
                <span class="font-serif text-xl tracking-[0.2em] font-semibold text-[#1C1917] uppercase">
                    MODEVA
                </span>
            </div>

            <span class="text-xs font-medium text-[#78716C] bg-[#EDE7DF] px-2.5 py-1 rounded-full">
                Petugas
            </span>
        </header>

        {{-- Main Content Area --}}
        <div class="lg:pl-72 lg:w-[calc(100%-18rem)] xl:pl-80 xl:w-[calc(100%-20rem)] min-h-screen flex flex-col justify-between">
            <main class="flex-1 px-4 sm:px-8 lg:px-10 py-8 lg:py-10 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <footer class="px-4 sm:px-8 lg:px-10 py-6 text-center text-xs text-[#A8A29E] border-t border-[#EFECE6]/60">
                &copy; {{ date('Y') }} Modeva Fashion Store. Hak cipta dilindungi.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
