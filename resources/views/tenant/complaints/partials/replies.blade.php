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

    <form method="POST" action="{{ route('tenant.complaints.reply', $complaint) }}" class="mt-5 grid gap-3">
        @csrf
        <textarea name="message" rows="3" required class="rounded-md border border-slate-300 px-3 py-2" placeholder="Tulis balasan"></textarea>
        @error('message') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        <button type="submit" class="rounded-md bg-[#061C5D] px-4 py-2 text-sm font-semibold text-white">Kirim Balasan</button>
    </form>
</section>
