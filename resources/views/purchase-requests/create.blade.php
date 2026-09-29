<x-app-layout>
    <x-slot name="title">Buat Purchase Request</x-slot>

    <div class="max-w-3xl bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('purchase-requests.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="branch_id" value="Cabang" />
                    <select id="branch_id" name="branch_id" required class="mt-1 w-full rounded-md border-gray-300">
                        @foreach (\App\Support\BranchScope::branches(auth()->user()) as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="request_date" value="Tanggal" />
                    <x-text-input id="request_date" name="request_date" type="date" :value="now()->toDateString()" required class="mt-1 w-full" />
                </div>
            </div>

            <x-item-rows name="items" :with-price="false" />

            <div class="mt-6 flex gap-3">
                <x-primary-button>Simpan</x-primary-button>
                <a href="{{ route('purchase-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>