<x-app-layout>
    <x-slot name="title">Keuangan</x-slot>

    <div class="space-y-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <x-input-label for="branch_id" value="Cabang" />
                <select id="branch_id" name="branch_id" class="mt-1 rounded-md border-gray-300 text-sm">
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected(request('branch_id', $summary['branch_id']) == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="from" value="Dari" />
                <x-text-input id="from" name="from" type="date" :value="request('from', $summary['from'])" class="mt-1" />
            </div>
            <div>
                <x-input-label for="to" value="Sampai" />
                <x-text-input id="to" name="to" type="date" :value="request('to', $summary['to'])" class="mt-1" />
            </div>
            <x-primary-button>Filter</x-primary-button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-gray-500">Pendapatan</div>
                <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-gray-500">Biaya</div>
                <div class="text-2xl font-bold text-red-600">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-gray-500">Laba / Rugi</div>
                <div class="text-2xl font-bold {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($summary['profit'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>