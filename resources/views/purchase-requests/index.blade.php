<x-app-layout>
    <x-slot name="title">Purchase Request</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <x-primary-button href="{{ route('purchase-requests.create') }}">Buat PR</x-primary-button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr><th class="px-4 py-3">PR</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Cabang</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">PR-{{ $r->id }}</td>
                            <td class="px-4 py-3">{{ $r->request_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $r->branch?->name }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ match($r->status) { 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' } }}">{{ $r->status }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if ($r->status === 'draft')
                                    <form method="POST" action="{{ route('purchase-requests.approve', $r) }}" class="inline">
                                        @csrf
                                        <button class="text-emerald-600 hover:text-emerald-800">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('purchase-requests.reject', $r) }}" class="inline ms-2">
                                        @csrf
                                        <button class="text-red-600 hover:text-red-800">Tolak</button>
                                    </form>
                                @elseif ($r->status === 'approved')
                                    <a href="{{ route('purchase.order.create') }}" class="text-slate-600 hover:text-slate-900">→ Buat PO</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada PR.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</x-app-layout>