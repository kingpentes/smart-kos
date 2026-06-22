<x-layouts.app title="Verifikasi Listing">
    <h1 class="text-2xl font-bold text-slate-900">Verifikasi Listing</h1>

    <div class="mt-6 overflow-hidden rounded-md border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Pemilik</th>
                    <th class="px-4 py-3">Kota</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($boardingHouses as $boardingHouse)
                    <tr class="border-t border-slate-200">
                        <td class="px-4 py-3 font-semibold">{{ $boardingHouse->name }}</td>
                        <td class="px-4 py-3">{{ $boardingHouse->owner->name }}</td>
                        <td class="px-4 py-3">{{ $boardingHouse->city }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($boardingHouse->price_monthly, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.listings.verify', $boardingHouse) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Publish</button>
                                </form>
                                <form method="POST" action="{{ route('admin.listings.reject', $boardingHouse) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold text-white">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-slate-600">Tidak ada listing pending.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $boardingHouses->links() }}</div>
</x-layouts.app>
