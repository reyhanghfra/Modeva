@extends('layouts.petugas')
@section('title', 'Ubah Password - MODEVA')
@section('content')
<div class="max-w-2xl space-y-6"><div><h1 class="font-serif text-3xl font-semibold">Ubah Password</h1><p class="text-sm text-stone-500 mt-1">Perbarui kata sandi akun petugas untuk menjaga keamanan akun.</p></div>
@if(session('success'))<div class="bg-emerald-50 text-emerald-800 p-3 rounded-xl">{{ session('success') }}</div>@endif
@if($errors->any())<div class="bg-rose-50 text-rose-800 p-3 rounded-xl">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('petugas.password.update') }}" class="bg-white border rounded-2xl p-6 space-y-4">@csrf @method('PUT')
<div><label class="block text-sm font-medium mb-1" for="current_password">Password saat ini</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full border rounded-lg px-3 py-2"></div>
<div><label class="block text-sm font-medium mb-1" for="password">Password baru</label><input id="password" name="password" type="password" required autocomplete="new-password" class="w-full border rounded-lg px-3 py-2"></div>
<div><label class="block text-sm font-medium mb-1" for="password_confirmation">Konfirmasi password baru</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full border rounded-lg px-3 py-2"></div>
<button class="bg-stone-900 text-white rounded-lg px-5 py-3">Simpan Password</button></form></div>
@endsection
