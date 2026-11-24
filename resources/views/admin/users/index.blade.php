@extends('layouts.admin')

@section('title', 'Data Pengguna - Dasbor Modeva')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Data Pengguna"
        :subtitle="$totalUsers . ' pengguna terdaftar di sistem'"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.users.create')" icon="plus">
                Tambah Pengguna
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Alert Notifikasi --}}
    <x-admin.flash />

    {{-- Kontrol Pencarian & Filter --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <x-admin.search-input placeholder="Cari nama atau email..." />

        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Peran:</span>
            <x-admin.filter-pills
                name="role"
                :options="['' => 'Semua', 'admin' => 'Admin', 'petugas' => 'Petugas', 'pelanggan' => 'Pelanggan']"
            />

            <span class="text-xs font-medium text-[#78716C] uppercase tracking-wider ml-2">Status:</span>
            <x-admin.filter-pills
                name="status"
                :options="['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']"
            />
        </div>
    </div>

    {{-- Tabel Pengguna --}}
    <x-admin.data-table
        :headers="[
            'Pengguna',
            'Peran',
            'Status',
            'Terdaftar',
            ['label' => 'Aksi', 'class' => 'text-right'],
        ]"
        :paginator="$users"
    >
        @forelse ($users as $user)
            @php
                $initials = collect(explode(' ', $user->name))
                    ->map(fn($part) => mb_substr($part, 0, 1))
                    ->take(2)
                    ->join('');

                $roleVariant = match ($user->role) {
                    'admin' => 'danger',
                    'petugas' => 'warning',
                    default => 'neutral',
                };
            @endphp
            <tr class="hover:bg-[#FAF8F5]/50 transition-colors">
                {{-- Nama & Email dengan Avatar Inisial --}}
                <td class="px-5 sm:px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#EDE7DF] border border-[#E5DDD2] flex items-center justify-center font-semibold text-xs text-[#1C1917] shrink-0 tracking-wide select-none">
                            {{ strtoupper($initials) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-[#1C1917] truncate flex items-center gap-2">
                                <span>{{ $user->name }}</span>
                                @if (auth()->id() === $user->id)
                                    <span class="text-[10px] font-medium bg-[#EDE7DF] text-[#78716C] px-2 py-0.5 rounded-full">Anda</span>
                                @endif
                            </div>
                            <div class="text-xs text-[#78716C] truncate mt-0.5">
                                {{ $user->email }}
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Peran / Role --}}
                <td class="px-5 sm:px-6 py-4">
                    <x-admin.status-badge
                        :variant="$roleVariant"
                        :label="ucfirst($user->role)"
                    />
                </td>

                {{-- Status Aktif / Nonaktif --}}
                <td class="px-5 sm:px-6 py-4">
                    <x-admin.status-badge
                        :variant="$user->is_active ? 'success' : 'danger'"
                        :label="$user->is_active ? 'Aktif' : 'Nonaktif'"
                    />
                </td>

                {{-- Tanggal Dibuat --}}
                <td class="px-5 sm:px-6 py-4 text-xs text-[#78716C] whitespace-nowrap">
                    {{ $user->created_at?->format('d M Y') ?? '-' }}
                </td>

                {{-- Tombol Aksi --}}
                <td class="px-5 sm:px-6 py-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        {{-- Tombol Cepat Toggle Status --}}
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="p-1.5 rounded-lg text-[#78716C] hover:text-[#1C1917] hover:bg-[#EDE7DF] transition-colors"
                                title="{{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                            >
                                <x-icon :name="$user->is_active ? 'x-circle' : 'check-circle'" class="w-4 h-4" strokeWidth="2" />
                            </button>
                        </form>

                        {{-- Tombol Edit --}}
                        <a
                            href="{{ route('admin.users.edit', $user) }}"
                            class="p-1.5 rounded-lg text-[#78716C] hover:text-[#1C1917] hover:bg-[#EDE7DF] transition-colors"
                            title="Ubah data pengguna"
                        >
                            <x-icon name="pencil" class="w-4 h-4" strokeWidth="2" />
                        </a>

                        {{-- Tombol Hapus --}}
                        <form
                            method="POST"
                            action="{{ route('admin.users.destroy', $user) }}"
                            class="inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors"
                                title="Hapus pengguna"
                            >
                                <x-icon name="trash" class="w-4 h-4" strokeWidth="2" />
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-[#78716C]">
                    <div class="max-w-xs mx-auto space-y-2">
                        <x-icon name="users" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="font-medium text-sm text-[#1C1917]">Tidak ada pengguna ditemukan</p>
                        <p class="text-xs text-[#78716C]">Coba ubah kata kunci pencarian atau bersihkan filter yang aktif.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-admin.data-table>
</div>
@endsection
