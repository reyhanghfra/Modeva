@php
    use Illuminate\Support\Facades\Schema;

    $pendingPaymentCount = 0;
    if (Schema::hasTable('payments')) {
        $pendingPaymentCount = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI)->count();
    }

    $menus = [
        [
            'name' => 'Ringkasan',
            'icon' => 'home',
            'route' => 'admin.dashboard',
        ],
        [
            'name' => 'Produk',
            'icon' => 'box',
            'route' => 'admin.products.index',
        ],
        [
            'name' => 'Kategori',
            'icon' => 'tag',
            'route' => 'admin.categories.index',
        ],
        [
            'name' => 'Inventori',
            'icon' => 'layers',
            'route' => 'admin.inventory.index',
        ],
        [
            'name' => 'Transaksi',
            'icon' => 'receipt',
            'route' => 'admin.transactions.index',
        ],
        [
            'name' => 'Verifikasi Pembayaran',
            'icon' => 'shield-check',
            'route' => 'admin.payments.index',
            'badge' => $pendingPaymentCount > 0 ? $pendingPaymentCount : null,
        ],
        [
            'name' => 'Pelanggan',
            'icon' => 'users',
            'route' => 'admin.customers.index',
        ],
        [
            'name' => 'Laporan',
            'icon' => 'bar-chart',
            'route' => 'admin.reports.index',
        ],
        [
            'name' => 'Pengguna',
            'icon' => 'users',
            'route' => 'admin.users.index',
        ],
        [
            'name' => 'Pengaturan',
            'icon' => 'settings',
            'route' => 'admin.password.edit',
        ],
    ];

    $currentUser = Auth::user();
    $roleLabel = ($currentUser && $currentUser->role === 'admin') ? 'Pemilik / Admin' : 'Staf / Petugas';
@endphp

<aside
    id="adminSidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 lg:w-80 bg-[#1F1F1E] text-[#FAF8F5] flex flex-col justify-between p-6 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-[#2C2B29] shadow-2xl lg:shadow-none"
    aria-label="Sidebar Menu"
>
    <div class="flex flex-col h-full justify-between">
        {{-- Brand Logo & Menu Items --}}
        <div>
            {{-- Top Header with Brand & Close Button (mobile) --}}
            <div class="flex items-center justify-between pb-6 mb-6 border-b border-[#2C2B29]">
                <div>
                    <h1 class="font-serif text-2xl tracking-[0.25em] font-semibold text-[#FAF8F5] uppercase">
                        MODEVA
                    </h1>
                    <p class="text-xs tracking-wider text-[#A8A29E] mt-1 font-medium">
                        Dasbor Admin
                    </p>
                </div>

                {{-- Close button for mobile drawer --}}
                <button
                    type="button"
                    id="adminSidebarCloseBtn"
                    class="lg:hidden p-2 rounded-lg text-[#A8A29E] hover:text-[#FAF8F5] hover:bg-[#2B2A28] focus:outline-none"
                    aria-label="Tutup Menu"
                >
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            {{-- Navigation Menu --}}
            <nav class="space-y-1" aria-label="Navigasi Utama">
                @foreach ($menus as $item)
                    @php
                        $hasRoute = Route::has($item['route']);
                        $url = $hasRoute ? route($item['route']) : '#';
                        $isActive = $hasRoute && (request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'));
                    @endphp

                    <a
                        href="{{ $url }}"
                        class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $isActive ? 'bg-[#33312E] text-white shadow-xs' : 'text-[#A8A29E] hover:text-[#FAF8F5] hover:bg-[#2B2A28]' }}"
                        @if ($isActive) aria-current="page" @endif
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <x-icon
                                :name="$item['icon']"
                                class="w-5 h-5 shrink-0 {{ $isActive ? 'text-white' : 'text-[#A8A29E]' }}"
                                strokeWidth="1.75"
                            />
                            <span class="truncate">{{ $item['name'] }}</span>
                        </div>

                        @if (!empty($item['badge']))
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold bg-amber-500 text-white rounded-full shrink-0">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Bottom: User Info & Logout Button --}}
        <div class="pt-6 border-t border-[#2C2B29] mt-6">
            <div class="bg-[#262523] rounded-2xl p-4 border border-[#33312E]">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white truncate" title="{{ $currentUser?->name ?? 'Admin' }}">
                            {{ $currentUser?->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-[#A8A29E] mt-0.5 truncate">
                            {{ $roleLabel }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button
                            type="submit"
                            title="Keluar dari akun"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-[#D0C5B8] bg-[#33312E] hover:bg-[#3D3A36] hover:text-white transition-colors focus:outline-none focus:ring-1 focus:ring-[#D0C5B8]"
                        >
                            <x-icon name="log-out" class="w-3.5 h-3.5" strokeWidth="1.75" />
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
