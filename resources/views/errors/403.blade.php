@extends('layouts.guest')

@section('title', '403 - Akses Ditolak - Modeva')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
    <p class="text-base font-semibold text-red-600">403</p>
    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
        Akses Ditolak
    </h1>
    <p class="mt-4 text-sm text-slate-600">
        Maaf, Anda tidak memiliki izin atau hak akses untuk membuka halaman ini.
    </p>

    @php
        $user = auth()->user();
        $targetRoute = ($user && $user->is_active) ? route($user->homeRoute()) : route('home');
    @endphp

    <div class="mt-8 flex justify-center">
        <a
            href="{{ $targetRoute }}"
            class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-colors"
        >
            Kembali ke Halaman Utama
        </a>
    </div>
</div>
@endsection
