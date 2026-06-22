<x-layouts.dashboard title="Dashboard Penyewa">
    @php
        $nextInvoice = $unpaidInvoices->first();
        $pendingBooking = $bookings->first(fn ($booking) => $booking->status === \App\Enums\BookingStatus::Pending);
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl">Halo, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h1>
        <p class="mt-1 text-sm text-slate-500">Berikut adalah ringkasan aktivitas sewa kos Anda hari ini.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-6 lg:gap-8">
        <!-- Main Content Column -->
        <div class="flex flex-col gap-6 lg:gap-8">
            <!-- Hero Section -->
            <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 p-1 shadow-lg shadow-blue-500/20 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 h-48 w-48 rounded-full bg-indigo-500/30 blur-3xl"></div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-[22px] p-6 sm:p-8 relative z-10 text-white border border-white/20">
                    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div>
                            @if ($nextInvoice)
                                <span class="inline-flex items-center rounded-full bg-rose-500/20 px-2.5 py-1 text-xs font-bold text-rose-100 border border-rose-400/30 shadow-sm backdrop-blur-md mb-3"><svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Tagihan Belum Dibayar</span>
                                <h2 class="text-2xl font-extrabold tracking-tight">{{ $nextInvoice->lease->boardingHouse->name }}</h2>
                                <p class="mt-1.5 text-sm text-indigo-100 font-medium">Rp{{ number_format($nextInvoice->amount, 0, ',', '.') }} jatuh tempo pada {{ $nextInvoice->due_date->format('d M Y') }}</p>
                            @elseif ($pendingBooking)
                                <span class="inline-flex items-center rounded-full bg-amber-500/20 px-2.5 py-1 text-xs font-bold text-amber-100 border border-amber-400/30 shadow-sm backdrop-blur-md mb-3">⏳ Menunggu Konfirmasi Pemilik</span>
                                <h2 class="text-2xl font-extrabold tracking-tight">{{ $pendingBooking->boardingHouse->name }}</h2>
                                <p class="mt-1.5 text-sm text-indigo-100 font-medium">Booking masuk. Anda dapat membayar setelah pemilik menerima booking.</p>
                            @elseif ($activeLease)
                                <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-100 border border-emerald-400/30 shadow-sm backdrop-blur-md mb-3">✓ Sewa Aktif</span>
                                <h2 class="text-2xl font-extrabold tracking-tight">{{ $activeLease->boardingHouse->name }}</h2>
                                <p class="mt-1.5 text-sm text-indigo-100 font-medium">Kamar {{ $activeLease->room->room_number }} sampai {{ $activeLease->end_date->format('d M Y') }}</p>
                            @else
                                <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-1 text-xs font-bold text-white border border-white/30 shadow-sm backdrop-blur-md mb-3">🚀 Ayo Mulai!</span>
                                <h2 class="text-2xl font-extrabold tracking-tight">Belum ada booking aktif</h2>
                                <p class="mt-1.5 text-sm text-indigo-100 font-medium">Temukan kos impianmu dan mulai pengalaman baru hari ini.</p>
                            @endif
                        </div>

                        @if ($nextInvoice)
                            <a href="{{ route('tenant.invoices.show', $nextInvoice) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-blue-600 hover:bg-slate-50 transition-colors shadow-lg shadow-black/10 active:scale-[0.98]">Bayar Sekarang</a>
                        @elseif (! $activeLease)
                            <a href="{{ route('boarding-houses.search') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-blue-600 hover:bg-slate-50 transition-colors shadow-lg shadow-black/10 active:scale-[0.98]">Cari Kos Sekarang</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Booking Section -->
            <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-900">Riwayat Booking Terbaru</h2>
                </div>

                <div class="grid gap-4">
                    @forelse ($bookings as $booking)
                        @php
                            $statusMeta = match ($booking->status) {
                                \App\Enums\BookingStatus::Pending => ['label' => 'Menunggu', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'note' => 'Menunggu pemilik.'],
                                \App\Enums\BookingStatus::Accepted => ['label' => 'Diterima', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'note' => 'Segera selesaikan pembayaran.'],
                                \App\Enums\BookingStatus::Rejected => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700 border-rose-200', 'note' => 'Cari kos lain.'],
                                \App\Enums\BookingStatus::Cancelled => ['label' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-700 border-slate-200', 'note' => 'Dibatalkan.'],
                                \App\Enums\BookingStatus::Converted => ['label' => 'Aktif', 'class' => 'bg-blue-50 text-blue-700 border-blue-200', 'note' => 'Sewa berjalan.'],
                            };
                            $bookingInvoice = $booking->lease?->invoices
                                ->first(fn ($invoice) => in_array($invoice->status, [\App\Enums\InvoiceStatus::Unpaid, \App\Enums\InvoiceStatus::Overdue], true));
                        @endphp

                        <div class="group rounded-2xl border border-slate-200/80 bg-white p-5 hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-100/50 transition-all duration-300">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="font-bold text-slate-900 text-base group-hover:text-indigo-700 transition-colors">{{ $booking->boardingHouse->name }}</div>
                                        <div class="text-sm font-medium text-slate-600">Kamar {{ $booking->room->room_number }} <span class="text-slate-300 mx-1">&bull;</span> Mulai {{ $booking->start_date->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $statusMeta['note'] }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                            </div>

                            @if ($bookingInvoice)
                                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                                    <x-ui.button href="{{ route('tenant.invoices.show', $bookingInvoice) }}" class="rounded-lg py-2 px-5 text-xs bg-slate-900 text-white hover:bg-slate-800 shadow-md">Bayar Tagihan Pertama</x-ui.button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12 rounded-2xl border border-dashed border-slate-200 bg-slate-50 flex flex-col items-center justify-center">
                            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-sm text-slate-500 font-medium">Belum ada aktivitas booking kos.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar / Widget Column -->
        <div class="flex flex-col gap-6 lg:gap-8">
            <!-- Active Lease Widget -->
            <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                <h2 class="text-lg font-bold text-slate-900 mb-5">Sewa Aktif Saya</h2>

                @if ($activeLease)
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm text-indigo-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            <div class="text-base font-bold text-slate-900 line-clamp-1">{{ $activeLease->boardingHouse->name }}</div>
                        </div>
                        
                        <div class="flex flex-col gap-3 text-sm text-slate-600 bg-white/60 p-4 rounded-xl border border-indigo-100/50">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-slate-500">Nomor Kamar</span>
                                <span class="font-bold text-slate-900 bg-white px-2 py-0.5 rounded shadow-sm border border-slate-100">{{ $activeLease->room->room_number }}</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-slate-200/50 pt-2">
                                <span class="font-medium text-slate-500">Akhir Sewa</span>
                                <span class="font-semibold text-slate-900">{{ $activeLease->end_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-slate-200/50 pt-2">
                                <span class="font-medium text-slate-500">Jatuh Tempo</span>
                                <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">{{ $activeLease->next_due_date->format('d M Y') }}</span>
                            </div>
                        </div>
                        @if (! $activeLease->review)
                            <div class="mt-5 pt-4 border-t border-indigo-200/60">
                                <a href="{{ route('tenant.reviews.create', ['lease_id' => $activeLease->id]) }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-indigo-700 shadow-sm border border-indigo-100 hover:bg-indigo-50 hover:border-indigo-200 transition-all active:scale-[0.98]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                    Berikan Ulasan Kos
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-sm font-medium text-slate-500 text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">Belum ada sewa aktif.</div>
                @endif
            </div>

            <!-- Unpaid Invoices Widget -->
            <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Tagihan Terbuka</h2>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-xs font-bold text-rose-600">{{ $unpaidInvoices->count() }}</span>
                </div>

                <div class="grid gap-3">
                    @forelse ($unpaidInvoices as $invoice)
                        <a href="{{ route('tenant.invoices.show', $invoice) }}" class="group flex flex-col gap-3 rounded-2xl border border-rose-100 bg-rose-50/30 p-4 hover:bg-rose-50 transition-colors">
                            <div class="flex justify-between items-start gap-2">
                                <div class="font-bold text-slate-900 text-sm line-clamp-1 group-hover:text-rose-700 transition-colors">{{ $invoice->lease->boardingHouse->name }}</div>
                                <div class="text-xs font-black text-rose-700 bg-white px-2 py-1 rounded-md shadow-sm border border-rose-100">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] font-mono text-slate-400">{{ $invoice->number }}</div>
                                <div class="text-xs font-bold text-rose-600 group-hover:translate-x-0.5 transition-transform">Bayar &rarr;</div>
                            </div>
                        </a>
                    @empty
                        <div class="text-sm font-medium text-slate-500 text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 flex flex-col items-center gap-2">
                            <span class="text-2xl">🎉</span>
                            Yeay! Semua tagihan lunas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Latest Complaints Widget -->
            <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Keluhan Terakhir</h2>
                    <a href="{{ route('tenant.complaints.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">+ Buat</a>
                </div>

                <div class="grid gap-3">
                    @forelse ($latestComplaints as $complaint)
                        <a href="{{ route('tenant.complaints.show', $complaint) }}" class="group block rounded-2xl border border-slate-100 bg-slate-50/50 p-4 hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-sm transition-all">
                            <div class="flex justify-between items-center mb-2 gap-2">
                                <div class="font-bold text-slate-900 text-sm truncate group-hover:text-indigo-700 transition-colors">{{ str_replace('_', ' ', $complaint->category) }}</div>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $complaint->status->value === 'open' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($complaint->status->value === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">{{ $complaint->status->value }}</span>
                            </div>
                            <div class="text-xs font-medium text-slate-500 truncate flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ $complaint->lease->boardingHouse->name }}
                            </div>
                        </a>
                    @empty
                        <div class="text-sm font-medium text-slate-500 text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">Belum ada keluhan diajukan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
