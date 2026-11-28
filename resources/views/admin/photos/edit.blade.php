@extends('layouts.admin')

@section('title', 'Foto Situs - Dasbor Modeva')

@section('content')
<div class="max-w-6xl mx-auto space-y-10">
    <x-admin.page-header title="Foto Situs" subtitle="Atur foto hero, banner promo, kategori, dan produk yang tampil di toko." />

    <x-admin.flash />

    @if ($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-800 font-medium">{{ $errors->first() }}</div>
    @endif

    @foreach ($groups as $title => $items)
        <section>
            <h2 class="font-serif text-2xl font-semibold text-[#1C1917] mb-4">{{ $title }}</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($items as $it)
                    @php
                        $src = $it['path'] && \Illuminate\Support\Facades\Storage::disk('public')->exists($it['path'])
                            ? \Illuminate\Support\Facades\Storage::url($it['path'])
                            : null;
                    @endphp
                    <form method="POST" action="{{ route('admin.photos.update') }}" enctype="multipart/form-data"
                          class="bg-white rounded-3xl p-4 border border-[#EFECE6] space-y-3">
                        @csrf
                        <input type="hidden" name="type" value="{{ $it['type'] }}">
                        @if ($it['id'])
                            <input type="hidden" name="id" value="{{ $it['id'] }}">
                        @endif

                        <div class="aspect-[4/3] rounded-2xl bg-[#EDE7DF] overflow-hidden flex items-center justify-center text-xs text-[#78716C]">
                            @if ($src)
                                <img src="{{ $src }}" alt="{{ $it['label'] }}" class="w-full h-full object-cover">
                            @else
                                Belum ada foto
                            @endif
                        </div>

                        <p class="text-sm font-medium text-[#1C1917] truncate">{{ $it['label'] }}</p>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required
                               class="block w-full text-xs text-[#78716C] file:mr-3 file:rounded-full file:border-0 file:bg-[#EDE7DF] file:px-4 file:py-2 file:text-xs file:font-medium file:text-[#1C1917]">
                        <x-admin.button type="submit" size="sm">Simpan Foto</x-admin.button>
                    </form>
                @empty
                    <p class="text-sm text-[#78716C]">Belum ada data.</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection