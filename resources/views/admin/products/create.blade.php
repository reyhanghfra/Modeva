@extends('layouts.admin')

@section('title', 'Tambah Produk - Dasbor Modeva')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-admin.page-header title="Tambah Produk" subtitle="Tambahkan produk baru beserta pilihan warna, ukuran, dan stok.">
        <x-slot:action>
            <x-admin.button :href="route('admin.products.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    @include('admin.products.partials.form', [
        'formAction' => route('admin.products.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Simpan Produk',
    ])
</div>
@endsection
