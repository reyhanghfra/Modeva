@extends('layouts.admin')

@section('title', 'Edit Produk - Dasbor Modeva')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-admin.page-header title="Edit Produk" :subtitle="'Perbarui informasi dan varian '.$product->name">
        <x-slot:action>
            <x-admin.button :href="route('admin.products.index')" variant="secondary" icon="arrow-left">
                Kembali
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    @include('admin.products.partials.form', [
        'formAction' => route('admin.products.update', $product),
        'formMethod' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
</div>
@endsection
