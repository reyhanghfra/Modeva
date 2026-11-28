<!DOCTYPE html>
<html lang="id" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk - Modeva')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen min-h-dvh flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800 antialiased bg-slate-50">
    <div class="w-full">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
