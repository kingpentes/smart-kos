<x-layouts.dashboard title="Dashboard Pemilik">
    <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl">Ringkasan Properti</h1>
            <p class="mt-1.5 text-sm font-medium text-slate-500">Pantau performa kos, tagihan, dan keluhan penyewa Anda hari ini.</p>
        </div>
        <x-ui.button href="{{ route('owner.listings.create') }}" class="flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition-all active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Tambah Listing Baru
        </x-ui.button>
    </div>

    <!-- Stats Grid -->
    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-100/50 transition-all">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-indigo-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Listing Properti</div>
            <div class="relative z-10 flex items-baseline gap-1.5">
                <div class="text-4xl font-black text-slate-900 tracking-tight">{{ $totalListings }}</div>
            </div>
            <div class="relative z-10 mt-4 flex items-center justify-between rounded-xl bg-slate-50 p-2.5 text-xs font-semibold text-slate-600 border border-slate-100">
                <span class="flex items-center gap-1.5 text-emerald-600"><span class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_0_2px_rgba(16,185,129,0.2)]"></span> {{ $publishedListings }} Aktif</span> 
                <span class="flex items-center gap-1.5 text-amber-600"><span class="flex h-2 w-2 rounded-full bg-amber-500 shadow-[0_0_0_2px_rgba(245,158,11,0.2)]"></span> {{ $pendingListings }} Pending</span>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-amber-300 hover:shadow-md hover:shadow-amber-100/50 transition-all">
            @if($pendingBookings > 0)
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-100/50 transition-transform group-hover:scale-110"></div>
                <div class="absolute top-4 right-4 flex h-3 w-3 items-center justify-center">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                </div>
            @endif
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Booking Menunggu</div>
            <div class="relative z-10 flex items-baseline gap-1.5">
                <div class="text-4xl font-black text-slate-900 tracking-tight">{{ $pendingBookings }}</div>
            </div>
            <div class="relative z-10 mt-4 rounded-xl border border-amber-100 bg-amber-50 transition-colors hover:bg-amber-100/50">
                <a href="{{ route('owner.bookings.index') }}" class="flex items-center justify-between p-2.5 text-xs font-bold text-amber-700">
                    Kelola booking &rarr;
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-100/50 transition-all">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Penyewa Aktif</div>
            <div class="relative z-10 flex items-baseline gap-1.5">
                <div class="text-4xl font-black text-slate-900 tracking-tight">{{ $activeLeases }}</div>
                <span class="text-sm font-bold text-slate-400">penyewa</span>
            </div>
            <div class="relative z-10 mt-4 rounded-xl border transition-colors {{ $openComplaints > 0 ? 'border-rose-100 bg-rose-50 hover:bg-rose-100/50' : 'border-slate-100 bg-slate-50 hover:bg-slate-100/50' }}">
                <a href="{{ route('owner.complaints.index') }}" class="flex items-center justify-between p-2.5 text-xs font-bold {{ $openComplaints > 0 ? 'text-rose-700' : 'text-slate-600' }}">
                    <span>Keluhan Terbuka</span>
                    <span class="flex h-5 items-center rounded-md bg-white px-2 shadow-sm border border-slate-100">{{ $openComplaints }}</span>
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-100/50 transition-all">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-indigo-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tagihan Lunas</div>
            <div class="relative z-10 flex items-baseline gap-1.5">
                <div class="text-4xl font-black text-slate-900 tracking-tight">{{ $paidInvoices }}</div>
                <span class="text-sm font-bold text-slate-400">faktur</span>
            </div>
            <div class="relative z-10 mt-4 rounded-xl border transition-colors {{ $unpaidInvoices > 0 ? 'border-amber-100 bg-amber-50 hover:bg-amber-100/50' : 'border-slate-100 bg-slate-50 hover:bg-slate-100/50' }}">
                <a href="{{ route('owner.invoices.index') }}" class="flex items-center justify-between p-2.5 text-xs font-bold {{ $unpaidInvoices > 0 ? 'text-amber-700' : 'text-slate-600' }}">
                    <span>Belum Lunas</span>
                    <span class="flex h-5 items-center rounded-md bg-white px-2 shadow-sm border border-slate-100">{{ $unpaidInvoices }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
        <!-- Latest Bookings -->
        <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-slate-900">Booking Terbaru</h2>
                <a href="{{ route('owner.bookings.index') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">Semua &rarr;</a>
            </div>
            
            <div class="flex flex-col gap-3">
                @forelse($latestBookings as $booking)
                    <div class="group flex items-center justify-between p-4 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 font-bold text-xs group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                {{ substr($booking->tenant->name, 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 text-sm truncate group-hover:text-indigo-700 transition-colors">{{ $booking->tenant->name }}</div>
                                <div class="text-[11px] font-medium text-slate-500 mt-0.5 truncate">{{ $booking->boardingHouse->name }} &bull; Kmr {{ $booking->room->room_number }}</div>
                            </div>
                        </div>
                        <div class="shrink-0 ml-4 flex flex-col items-end gap-1.5">
                            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border {{ $booking->status->value === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($booking->status->value === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-700 border-slate-200') }}">
                                {{ $booking->status->value }}
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">{{ $booking->start_date->format('d M y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50">
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-slate-500">Belum ada booking masuk.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Latest Complaints -->
        <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-slate-900">Keluhan Terbaru</h2>
                <a href="{{ route('owner.complaints.index') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">Semua &rarr;</a>
            </div>
            
            <div class="flex flex-col gap-3">
                @forelse($latestComplaints as $complaint)
                    <div class="group flex items-center justify-between p-4 rounded-2xl border border-slate-100 hover:border-rose-200 hover:bg-rose-50/30 transition-all">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 group-hover:bg-rose-100 group-hover:text-rose-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 text-sm truncate group-hover:text-rose-700 transition-colors">{{ str_replace('_', ' ', $complaint->category) }}</div>
                                <div class="text-[11px] font-medium text-slate-500 mt-0.5 truncate">{{ $complaint->tenant->name }} &bull; {{ $complaint->lease->boardingHouse->name }}</div>
                            </div>
                        </div>
                        <div class="shrink-0 ml-4 flex items-end">
                            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border {{ $complaint->status->value === 'open' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($complaint->status->value === 'in_progress' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                {{ str_replace('_', ' ', $complaint->status->value) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50">
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-slate-500">Luar biasa! Tidak ada keluhan.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
