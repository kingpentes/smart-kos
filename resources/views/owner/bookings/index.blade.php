<x-layouts.app title="Booking Masuk">
    <h1 class="text-2xl font-bold text-slate-900">Booking Masuk</h1>

    <div class="mt-6 overflow-hidden rounded-md border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3">Penyewa</th>
                    <th class="px-4 py-3">Kos</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Mulai</th>
                    <th class="px-4 py-3">Durasi</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr class="border-t border-slate-200">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $booking->tenant->name }}</div>
                            <div class="text-xs text-slate-600">{{ $booking->tenant->email }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $booking->boardingHouse->name }}</td>
                        <td class="px-4 py-3">{{ $booking->room->room_number }}</td>
                        <td class="px-4 py-3">{{ $booking->start_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $booking->duration_months }} bulan</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('owner.bookings.accept', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Terima</button>
                                </form>
                                <form method="POST" action="{{ route('owner.bookings.reject', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold text-white">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-slate-600">Belum ada booking masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
</x-layouts.app>
