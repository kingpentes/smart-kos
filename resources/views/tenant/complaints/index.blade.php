<x-layouts.app title="Keluhan Saya">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Keluhan Saya</h1>
        <a href="{{ route('tenant.complaints.create') }}" class="rounded-md bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white">Ajukan Keluhan</a>
    </div>

    <div class="mt-6 grid gap-3">
        @forelse ($complaints as $complaint)
            <a href="{{ route('tenant.complaints.show', $complaint) }}" class="rounded-md border border-slate-200 bg-white p-4 hover:border-[#061C5D]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $complaint->lease->boardingHouse->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">Kamar {{ $complaint->lease->room->room_number }} - {{ str_replace('_', ' ', $complaint->category) }}</div>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $complaint->status->value }}</span>
                </div>
                <p class="mt-3 text-sm text-slate-700">{{ str($complaint->description)->limit(140) }}</p>
            </a>
        @empty
            <div class="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">Belum ada keluhan.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $complaints->links() }}</div>
</x-layouts.app>
