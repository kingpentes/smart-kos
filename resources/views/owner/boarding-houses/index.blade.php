<x-layouts.dashboard title="Kelola Kos">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Kos</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar properti kos yang Anda miliki.</p>
        </div>
        <x-ui.button href="{{ route('owner.listings.create') }}">Tambah Kos</x-ui.button>
    </div>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kota</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($boardingHouses as $boardingHouse)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-900">{{ $boardingHouse->name }}</div>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $boardingHouse->city }}</td>
                        <td class="px-4 py-4">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 font-medium text-xs">
                                {{ $boardingHouse->rooms_count }} Kamar
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($boardingHouse->status->value === 'published')
                                <x-ui.badge variant="success">Aktif</x-ui.badge>
                            @elseif($boardingHouse->status->value === 'draft')
                                <x-ui.badge variant="muted">Draft</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">{{ ucfirst($boardingHouse->status->value) }}</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right flex justify-end gap-2">
                            <x-ui.button href="{{ route('owner.listings.show', $boardingHouse) }}" variant="secondary" class="text-xs px-2 py-1">Detail</x-ui.button>
                            <x-ui.button href="{{ route('owner.listings.edit', $boardingHouse) }}" class="text-xs px-2 py-1 bg-slate-100 text-slate-700 hover:bg-slate-200">Edit</x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-slate-600">Belum ada listing.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-ui.card>

    @if($boardingHouses->hasPages())
        <div class="mt-6">{{ $boardingHouses->links() }}</div>
    @endif
</x-layouts.dashboard>
