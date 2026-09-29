<x-app-layout>
    <x-slot name="title">Notifikasi</x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <form method="POST" action="{{ route('notifications.mark-read') }}">
                @csrf
                <button class="text-sm text-slate-600 hover:text-slate-900">Tandai semua dibaca</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
            @forelse ($notifications as $n)
                <div class="flex items-start gap-3 px-4 py-3 {{ $n->read_at ? 'opacity-60' : '' }}">
                    <span class="mt-1 h-2 w-2 rounded-full shrink-0 {{ match($n->severity) { 'critical' => 'bg-red-500', 'warning' => 'bg-amber-500', default => 'bg-blue-500' } }}"></span>
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $n->title }}</div>
                        @if($n->body)<div class="text-sm text-gray-500">{{ $n->body }}</div>@endif
                        <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-400">Belum ada notifikasi.</div>
            @endforelse
        </div>
        {{ $notifications->links() }}
    </div>
</x-app-layout>