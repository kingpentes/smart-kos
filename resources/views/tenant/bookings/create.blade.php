<x-layouts.app title="Booking {{ $boardingHouse->name }}">
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <x-ui.card class="p-6">
            <h1 class="text-2xl font-bold text-slate-900">Booking {{ $boardingHouse->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $boardingHouse->address }}</p>

            <form method="POST" action="{{ route('tenant.bookings.store', $boardingHouse) }}" class="mt-6 grid gap-4">
                @csrf

                <div class="grid gap-1">
                    <x-ui.label for="room_id">Pilih kamar</x-ui.label>
                    <x-ui.select name="room_id" id="room_id" required>
                        @foreach ($boardingHouse->availableRooms as $room)
                            <option value="{{ $room->id }}" @selected((int) old('room_id') === $room->id)>
                                Kamar {{ $room->room_number }} - Rp{{ number_format($room->price_monthly, 0, ',', '.') }}/bulan
                            </option>
                        @endforeach
                    </x-ui.select>
                    @error('room_id') <x-ui.error>{{ $message }}</x-ui.error> @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-1">
                        <x-ui.label for="start_date">Tanggal mulai</x-ui.label>
                        <x-ui.input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->addDays(7)->toDateString()) }}" required />
                        @error('start_date') <x-ui.error>{{ $message }}</x-ui.error> @enderror
                    </div>

                    <div class="grid gap-1">
                        <x-ui.label for="duration_months">Durasi</x-ui.label>
                        <x-ui.select name="duration_months" id="duration_months" required>
                            <option value="1" @selected((int) old('duration_months', 1) === 1)>1 bulan</option>
                            <option value="3" @selected((int) old('duration_months') === 3)>3 bulan</option>
                            <option value="6" @selected((int) old('duration_months') === 6)>6 bulan</option>
                            <option value="12" @selected((int) old('duration_months') === 12)>12 bulan</option>
                        </x-ui.select>
                        @error('duration_months') <x-ui.error>{{ $message }}</x-ui.error> @enderror
                    </div>
                </div>

                <div class="grid gap-1">
                    <x-ui.label for="notes">Catatan untuk pemilik</x-ui.label>
                    <x-ui.textarea name="notes" id="notes" rows="4">{{ old('notes') }}</x-ui.textarea>
                    @error('notes') <x-ui.error>{{ $message }}</x-ui.error> @enderror
                </div>

                @if ($boardingHouse->availableRooms->isEmpty())
                    <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">Tidak ada kamar tersedia.</div>
                @else
                    <x-ui.button type="submit" class="mt-2">Ajukan booking</x-ui.button>
                @endif
            </form>
        </x-ui.card>

        <aside class="self-start">
            <x-ui.card>
                <h2 class="text-lg font-semibold text-slate-950">Ringkasan booking</h2>
                <div class="mt-4 text-2xl font-bold text-[#2563EB]">Rp{{ number_format($boardingHouse->price_monthly, 0, ',', '.') }}/bulan</div>
                <div class="mt-4 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800 border border-amber-200">
                    Belum perlu bayar sekarang. Setelah pemilik menerima booking, tagihan akan muncul di dashboard penyewa.
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($boardingHouse->facilities as $facility)
                        <x-ui.badge variant="muted">{{ $facility->name }}</x-ui.badge>
                    @endforeach
                </div>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
