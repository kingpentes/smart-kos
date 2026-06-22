<x-layouts.app title="Keluhan Masuk">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Keluhan Masuk</h1>

        <form method="GET" action="{{ route('owner.complaints.index') }}" class="flex gap-2">
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-[#061C5D] px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
    </div>

    <div class="mt-6 grid gap-3">
        @forelse ($complaints as $complaint)
            <a href="{{ route('owner.complaints.show', $complaint) }}" class="rounded-md border border-slate-200 bg-white p-4 hover:border-[#061C5D]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $complaint->tenant->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $complaint->lease->boardingHouse->name }} - Kamar {{ $complaint->lease->room->room_number }}</div>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $complaint->status->value }}</span>
                </div>
                <p class="mt-3 text-sm text-slate-700">{{ str($complaint->description)->limit(140) }}</p>
            </a>
        @empty
            <div class="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">Belum ada keluhan masuk.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $complaints->links() }}</div>
</x-layouts.app>
