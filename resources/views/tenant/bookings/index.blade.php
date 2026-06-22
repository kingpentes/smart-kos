<x-layouts.dashboard title="Riwayat Booking">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Booking</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau status seluruh booking Anda.</p>
        </div>
        <x-ui.button href="{{ route('boarding-houses.search') }}" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Cari Kos Baru
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">TANGGAL</th>
                        <th class="px-4 py-3 font-semibold">KOS</th>
                        <th class="px-4 py-3 font-semibold">KAMAR</th>
                        <th class="px-4 py-3 font-semibold">STATUS</th>
                        <th class="px-4 py-3 font-semibold">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-600">{{ $booking->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $booking->boardingHouse->name }}</div>
                            </td>
                            <td class="px-4 py-4 text-slate-600">Kamar {{ $booking->room->room_number }}</td>
                            <td class="px-4 py-4">
                                @if($booking->status->value === 'pending')
                                    <x-ui.badge variant="warning">Menunggu Konfirmasi</x-ui.badge>
                                @elseif($booking->status->value === 'accepted')
                                    <x-ui.badge variant="success">Diterima</x-ui.badge>
                                @elseif($booking->status->value === 'rejected')
                                    <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                @else
                                    <x-ui.badge>{{ $booking->status->value }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($booking->invoice)
                                    <x-ui.button href="{{ route('tenant.invoices.show', $booking->invoice) }}" variant="secondary" class="text-xs px-2 py-1">
                                        Lihat Tagihan
                                    </x-ui.button>
                                @else
                                    <span class="text-slate-400 text-xs italic">Belum ada tagihan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                Anda belum memiliki riwayat booking kos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.dashboard>
