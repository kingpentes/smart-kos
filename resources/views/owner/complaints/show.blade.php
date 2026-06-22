<x-layouts.app title="Detail Keluhan">
    <section class="rounded-md border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-slate-600">{{ $complaint->tenant->name }} - {{ $complaint->lease->boardingHouse->name }}</div>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ str_replace('_', ' ', $complaint->category) }}</h1>
            </div>
            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">{{ $complaint->status->value }}</span>
        </div>

        <p class="mt-6 text-slate-700">{{ $complaint->description }}</p>

        <form method="POST" action="{{ route('owner.complaints.status', $complaint) }}" class="mt-6 flex flex-wrap gap-3">
            @csrf
            @method('PATCH')
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($complaint->status === $status)>{{ $status->value }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-[#061C5D] px-4 py-2 text-sm font-semibold text-white">Update Status</button>
        </form>
    </section>

    <section class="mt-6 rounded-md border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-slate-900">Percakapan</h2>

        <div class="mt-4 grid gap-3">
            @forelse ($complaint->replies as $reply)
                <div class="rounded-md bg-slate-50 p-3 text-sm">
                    <div class="font-semibold text-slate-900">{{ $reply->user->name }}</div>
                    <div class="mt-1 text-slate-700">{{ $reply->message }}</div>
                </div>
            @empty
                <div class="text-sm text-slate-600">Belum ada balasan.</div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('owner.complaints.reply', $complaint) }}" class="mt-5 grid gap-3">
            @csrf
            <textarea name="message" rows="3" required class="rounded-md border border-slate-300 px-3 py-2" placeholder="Tulis balasan"></textarea>
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tidak ubah status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
            @error('message') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            <button type="submit" class="rounded-md bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white">Kirim Balasan</button>
        </form>
    </section>
</x-layouts.app>
