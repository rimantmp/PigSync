{{-- Filter + tombol export, dipakai kelima halaman laporan. --}}
<form method="GET" class="flex flex-wrap gap-2 items-end mb-4">
    <div>
        <x-input-label for="branch_id" value="Cabang" />
        <select id="branch_id" name="branch_id" class="mt-1 rounded-md border-gray-300 text-sm">
            <option value="">Semua cabang</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" @selected((int) request('branch_id') === $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="from" value="Dari" />
        <x-text-input id="from" name="from" type="date" :value="request('from')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="to" value="Sampai" />
        <x-text-input id="to" name="to" type="date" :value="request('to')" class="mt-1" />
    </div>
    <x-primary-button>Filter</x-primary-button>
    @if (request()->hasAny(['branch_id', 'from', 'to']))
        <a href="{{ url()->current() }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
    @endif
</form>
