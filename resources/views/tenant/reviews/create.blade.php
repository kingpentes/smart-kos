<x-layouts.app title="Buat Ulasan - SMART KOST">
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-slate-900">Buat Ulasan</h1>

        <form method="POST" action="{{ route('tenant.reviews.store') }}" class="mt-6 grid gap-5 rounded-md border border-slate-200 bg-white p-6">
            @csrf

            <label class="grid gap-1 text-sm">
                <span>Sewa</span>
                <select name="lease_id" required class="rounded-md border border-slate-300 px-3 py-2">
                    <option value="">Pilih sewa</option>
                    @foreach ($leases as $lease)
                        <option value="{{ $lease->id }}" @selected((int) old('lease_id', $selectedLeaseId) === $lease->id)>
                            {{ $lease->boardingHouse->name }} - Kamar {{ $lease->room->room_number }}
                        </option>
                    @endforeach
                </select>
                @error('lease_id')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="grid gap-4 md:grid-cols-3">
                <label class="grid gap-1 text-sm">
                    <span>Kebersihan</span>
                    <select name="cleanliness_rating" required class="rounded-md border border-slate-300 px-3 py-2">
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((int) old('cleanliness_rating', 5) === $rating)>{{ $rating }}</option>
                        @endfor
                    </select>
                    @error('cleanliness_rating')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="grid gap-1 text-sm">
                    <span>Keamanan</span>
                    <select name="security_rating" required class="rounded-md border border-slate-300 px-3 py-2">
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((int) old('security_rating', 5) === $rating)>{{ $rating }}</option>
                        @endfor
                    </select>
                    @error('security_rating')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="grid gap-1 text-sm">
                    <span>Kesesuaian foto</span>
                    <select name="photo_match_rating" required class="rounded-md border border-slate-300 px-3 py-2">
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((int) old('photo_match_rating', 5) === $rating)>{{ $rating }}</option>
                        @endfor
                    </select>
                    @error('photo_match_rating')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <label class="grid gap-1 text-sm">
                <span>Komentar</span>
                <textarea name="comment" rows="4" class="rounded-md border border-slate-300 px-3 py-2">{{ old('comment') }}</textarea>
                @error('comment')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tenant.dashboard') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Batal</a>
                <button type="submit" class="rounded-md bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white">Kirim Ulasan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
