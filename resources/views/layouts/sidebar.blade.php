<aside x-data="{ open: false, master: false, farm: false }" class="w-64 shrink-0 bg-slate-900 text-slate-100 min-h-screen flex flex-col">
    <div class="flex items-center justify-between px-4 py-4 border-b border-slate-800">
        <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight">🐷 Sistem Kandang</a>
        <button @click="open = !open" class="md:hidden text-slate-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <nav :class="open ? 'flex' : 'hidden'" class="flex-col flex-1 overflow-y-auto px-3 py-3 space-y-1 text-sm md:flex">
        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
            <span>📊</span><span class="ms-2">Dashboard</span>
        </a>

        <button @click="master = !master" class="w-full flex items-center justify-between px-3 py-2 rounded-md hover:bg-slate-800 text-slate-300">
            <span>🗂️ Master Data</span>
            <svg class="h-4 w-4" :class="master && 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div x-show="master" class="ps-4 space-y-1">
            @foreach ([
                'branches' => 'Cabang', 'areas' => 'Area', 'pens' => 'Kandang',
                'breeds' => 'Jenis Babi', 'phases' => 'Fase Babi', 'units' => 'Satuan',
                'feed-types' => 'Jenis Pakan', 'medicines' => 'Obat & Vaksin', 'diseases' => 'Penyakit',
                'suppliers' => 'Supplier', 'customers' => 'Pelanggan',
            ] as $route => $label)
                <a href="{{ route($route.'.index') }}" class="block px-3 py-1.5 rounded hover:bg-slate-800 text-slate-400 {{ request()->routeIs($route.'.*') ? 'text-white' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <button @click="farm = !farm" class="w-full flex items-center justify-between px-3 py-2 rounded-md hover:bg-slate-800 text-slate-300">
            <span>🐖 Peternakan</span>
            <svg class="h-4 w-4" :class="farm && 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div x-show="farm" class="ps-4 space-y-1">
            @foreach ([
                'pigs' => 'Data Babi', 'population' => 'Populasi', 'weighings' => 'Penimbangan',
                'movements' => 'Perpindahan', 'health' => 'Kesehatan', 'reproduction' => 'Reproduksi',
                'births' => 'Kelahiran', 'deaths' => 'Kematian', 'feeds' => 'Pakan',
            ] as $route => $label)
                <a href="{{ route($route.'.index') }}" class="block px-3 py-1.5 rounded hover:bg-slate-800 text-slate-400 {{ request()->routeIs($route.'.*') ? 'text-white' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </nav>
</aside>