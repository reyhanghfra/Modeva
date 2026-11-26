@php
    $transactionsRoute = Route::has('petugas.transactions.index')
        ? 'petugas.transactions.index'
        : (Route::has('admin.transactions.index') ? 'admin.transactions.index' : null);

    $menus = [
        [
            'name' => 'Dasbor Operasional',
            'icon' => 'home',
            'route' => 'petugas.dashboard',
        ],
        [
            'name' => 'Data Transaksi',
            'icon' => 'receipt',
            'route' => $transactionsRoute,
        ],
    ];

    $currentUser = Auth::user();
@endphp

<aside
    id="adminSidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 lg:w-80 bg-[#1F1F1E] text-[#FAF8F5] flex flex-col justify-between p-6 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-[#2C2B29] shadow-2xl lg:shadow-none"
    aria-label="Sidebar Petugas"
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
                        Dasbor Petugas
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
            <nav class="space-y-1" aria-label="Navigasi Utama Petugas">
                @foreach ($menus as $item)
                    @php
                        $routeName = $item['route'];
                        $hasRoute = $routeName && Route::has($routeName);
                        $url = $hasRoute ? route($routeName) : '#';
                        $isActive = $hasRoute && (request()->routeIs($routeName) || request()->routeIs($routeName . '.*'));
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
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Bottom: User Info & Logout Button --}}
        <div class="pt-6 border-t border-[#2C2B29] mt-6">
            <div class="bg-[#262523] rounded-2xl p-4 border border-[#33312E]">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white truncate" title="{{ $currentUser?->name ?? 'Petugas' }}">
                            {{ $currentUser?->name ?? 'Petugas' }}
                        </p>
                        <p class="text-xs text-[#A8A29E] mt-0.5 truncate">
                            Staf / Petugas
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button
                            type="submit"
                            title="Keluar dari akun"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-[#D0C5B8] bg-[#33312E] hover:bg-[#3D3A36] hover:text-white transition-colors focus:outline-none focus:ring-1 focus:ring-[#D0C5B8] cursor-pointer"
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
