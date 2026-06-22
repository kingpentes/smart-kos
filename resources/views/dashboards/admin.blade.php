<x-layouts.dashboard title="Dashboard Admin">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Sistem Pusat Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola pendaftaran pengguna dan verifikasi kos</p>
        </div>
        <div class="flex gap-3">
            <x-ui.button href="{{ route('admin.users.index') }}" variant="secondary">Kelola Pengguna</x-ui.button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="mt-6 grid gap-4 md:grid-cols-3 mb-8">
        <x-ui.card class="border-t-4 border-blue-500 hover:shadow-md transition-shadow">
            <div class="text-sm font-bold text-slate-600 uppercase tracking-wider">Total Pengguna Aktif</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-4xl font-black text-slate-900">{{ $totalUsers }}</div>
            </div>
            <div class="mt-3 text-xs font-semibold text-slate-500">
                <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">Lihat semua pengguna &rarr;</a>
            </div>
        </x-ui.card>
        
        <x-ui.card class="border-t-4 border-amber-500 hover:shadow-md transition-shadow relative overflow-hidden">
            @if($pendingListings > 0)
                <div class="absolute top-0 right-0 w-16 h-16 bg-amber-100 rounded-bl-full z-0"></div>
                <div class="absolute top-2 right-2 w-3 h-3 bg-amber-500 rounded-full animate-pulse z-10"></div>
            @endif
            <div class="text-sm font-bold text-slate-600 uppercase tracking-wider relative z-10">Listing Pending</div>
            <div class="mt-2 text-4xl font-black text-slate-900 relative z-10">{{ $pendingListings }}</div>
            <div class="mt-3 text-xs font-semibold relative z-10">
                <a href="{{ route('admin.listings.index') }}" class="text-amber-600 hover:text-amber-800 flex items-center gap-1">Review sekarang &rarr;</a>
            </div>
        </x-ui.card>
        
        <x-ui.card class="border-t-4 border-emerald-500 hover:shadow-md transition-shadow">
            <div class="text-sm font-bold text-slate-600 uppercase tracking-wider">Listing Published</div>
            <div class="mt-2 text-4xl font-black text-slate-900">{{ $publishedListings }}</div>
            <div class="mt-3 text-xs font-semibold text-slate-500">
                <a href="{{ route('admin.listings.index') }}" class="text-emerald-600 hover:text-emerald-800">Kelola listing &rarr;</a>
            </div>
        </x-ui.card>
    </div>

    <!-- Pending Listings Action -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card>
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Menunggu Verifikasi</h2>
                    <a href="{{ route('admin.listings.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Semua Listing &rarr;</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 font-semibold rounded-tl-lg">Nama Kos</th>
                                <th class="px-4 py-3 font-semibold">Pemilik</th>
                                <th class="px-4 py-3 font-semibold text-right rounded-tr-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($latestPendingListings as $listing)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $listing->name }}</td>
                                    <td class="px-4 py-3">{{ $listing->owner->name }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.listings.verify', $listing) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-3 py-1.5 rounded text-xs font-bold transition-colors">Setuju</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.listings.reject', $listing) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded text-xs font-bold transition-colors">Tolak</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500 bg-slate-50 rounded-b-lg border border-dashed border-slate-200 border-t-0">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Semua listing sudah diverifikasi.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
        
        <div>
            <!-- Quick Info/Links Widget -->
            <x-ui.card class="bg-gradient-to-br from-[#061C5D] to-blue-900 text-white shadow-lg shadow-blue-900/20">
                <h2 class="text-lg font-bold mb-2">Panduan Verifikasi</h2>
                <p class="text-sm text-blue-100 mb-4 opacity-90">Pastikan hal-hal berikut saat memverifikasi kos baru:</p>
                <ul class="text-sm text-blue-50 space-y-2 mb-6">
                    <li class="flex gap-2 items-start"><svg class="w-5 h-5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Alamat dan peta jelas.</li>
                    <li class="flex gap-2 items-start"><svg class="w-5 h-5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Memiliki setidaknya 1 foto yang layak.</li>
                    <li class="flex gap-2 items-start"><svg class="w-5 h-5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Harga wajar dan deskripsi detail.</li>
                </ul>
                <a href="{{ route('admin.listings.index') }}" class="block w-full text-center bg-white text-[#061C5D] hover:bg-blue-50 font-bold py-2 px-4 rounded transition-colors text-sm">Review Semua Kos Sekarang</a>
            </x-ui.card>
        </div>
    </div>
</x-layouts.dashboard>
