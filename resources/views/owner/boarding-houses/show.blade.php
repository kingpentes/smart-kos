<x-layouts.dashboard title="Detail Kos - {{ $boardingHouse->name }}">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Kos: {{ $boardingHouse->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Manajemen detail kamar dan melihat identitas penyewa.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('owner.listings.index') }}" variant="secondary">Kembali</x-ui.button>
            <x-ui.button href="{{ route('owner.listings.edit', $boardingHouse) }}">Edit Informasi</x-ui.button>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card>
                <div class="text-sm font-semibold text-slate-500 uppercase">Total Kamar</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $boardingHouse->rooms->count() }}</div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-sm font-semibold text-emerald-600 uppercase">Kamar Terisi</div>
                <div class="mt-2 text-3xl font-bold text-emerald-700">
                    {{ $boardingHouse->rooms->where('status.value', 'occupied')->count() }}
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-sm font-semibold text-blue-600 uppercase">Kamar Tersedia</div>
                <div class="mt-2 text-3xl font-bold text-blue-700">
                    {{ $boardingHouse->rooms->where('status.value', 'available')->count() }}
                </div>
            </x-ui.card>
        </div>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 mb-4 border-b border-slate-100 pb-3">Daftar Kamar & Penyewa</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold">NO. KAMAR</th>
                            <th class="px-4 py-3 font-semibold">HARGA / BULAN</th>
                            <th class="px-4 py-3 font-semibold">STATUS</th>
                            <th class="px-4 py-3 font-semibold">IDENTITAS PENYEWA</th>
                            <th class="px-4 py-3 font-semibold">MASA SEWA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($boardingHouse->rooms as $room)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4 font-semibold text-slate-900">Kamar {{ $room->room_number }}</td>
                                <td class="px-4 py-4 text-slate-600">Rp{{ number_format($room->price_monthly, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    @if($room->status->value === 'available')
                                        <x-ui.badge variant="success">Tersedia</x-ui.badge>
                                    @elseif($room->status->value === 'occupied')
                                        <x-ui.badge variant="primary">Terisi</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="warning">{{ ucfirst($room->status->value) }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($room->activeLease && $room->activeLease->tenant)
                                        <div class="font-medium text-slate-900">{{ $room->activeLease->tenant->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $room->activeLease->tenant->email }}</div>
                                        @if($room->activeLease->tenant->phone_number)
                                            <div class="text-xs text-slate-500">{{ $room->activeLease->tenant->phone_number }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic text-xs">- Kosong -</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($room->activeLease)
                                        <div class="text-slate-900 text-sm">
                                            {{ $room->activeLease->start_date->format('d M Y') }} - {{ $room->activeLease->end_date->format('d M Y') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada kamar yang terdaftar. Edit informasi kos untuk menambah kamar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
