<x-layouts.app title="Detail Keluhan">
    <section class="rounded-md border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-slate-600">{{ $complaint->lease->boardingHouse->name }} - Kamar {{ $complaint->lease->room->room_number }}</div>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ str_replace('_', ' ', $complaint->category) }}</h1>
            </div>
            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">{{ $complaint->status->value }}</span>
        </div>

        <p class="mt-6 text-slate-700">{{ $complaint->description }}</p>
    </section>

    @include('tenant.complaints.partials.replies')
</x-layouts.app>
