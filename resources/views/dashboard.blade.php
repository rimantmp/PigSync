<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500">Total Populasi</div>
            <div class="text-3xl font-bold text-slate-900">{{ number_format($totalPopulasi) }}</div>
            <div class="text-xs text-gray-400 mt-1">ekor hidup</div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500">Sakit / Karantina</div>
            <div class="text-3xl font-bold text-amber-600">{{ number_format($sakit) }}</div>
            <div class="text-xs text-gray-400 mt-1">ekor</div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500">Kematian (bulan ini)</div>
            <div class="text-3xl font-bold text-red-600">{{ number_format($mati) }}</div>
            <div class="text-xs text-gray-400 mt-1">ekor</div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500">Kelahiran (bulan ini)</div>
            <div class="text-3xl font-bold text-emerald-600">{{ number_format($lahir) }}</div>
            <div class="text-xs text-gray-400 mt-1">piglet hidup</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <a href="{{ route('pigs.create') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">➕ Registrasi Ternak</a>
                <a href="{{ route('weighings.create') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">⚖️ Penimbangan</a>
                <a href="{{ route('movements.create') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">🔄 Perpindahan</a>
                <a href="{{ route('births.create') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">🐷 Kelahiran</a>
                <a href="{{ route('deaths.create') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">⚠️ Kematian</a>
                <a href="{{ route('feeds.index') }}" class="p-3 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700">🥣 Pakan</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Informasi</h3>
            <p class="text-sm text-gray-500">Populasi dihitung dari transaksi (registrasi, kelahiran, perpindahan, kematian) — bukan input manual. Rekonsiliasi di menu Peternakan → Populasi.</p>
        </div>
    </div>
</x-app-layout>