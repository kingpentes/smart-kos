<x-layouts.app title="SMART KOST">
    <section class="grid gap-12 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge>✨ AI Powered Finder</x-ui.badge>
                <x-ui.badge variant="muted">Terverifikasi</x-ui.badge>
            </div>

            <h1 class="mt-6 text-5xl font-extrabold leading-[1.1] text-slate-900 tracking-tight lg:text-6xl">
                Cari kos idaman<br>
                <span class="text-blue-600">tanpa ribet.</span>
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600">
                Temukan hunian sesuai fasilitas, lokasi, dan budget Anda. Dapatkan rekomendasi cerdas dari sistem AI kami yang dirancang untuk penyewa modern.
            </p>

            <form method="GET" action="{{ route('boarding-houses.search') }}" class="mt-8">
                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/60 bg-white p-3 shadow-sm shadow-slate-200/40 md:flex-row md:items-center">
                    <div class="flex-1">
                        <input name="prompt" type="text" placeholder="Kos dekat kampus dengan WiFi..." class="w-full bg-transparent px-3 py-2 text-sm text-slate-900 outline-none placeholder:text-slate-400" />
                    </div>
                    <div class="h-px w-full bg-slate-100 md:h-8 md:w-px"></div>
                    <div class="w-full md:w-1/3">
                        <input name="location" type="text" placeholder="Lokasi" class="w-full bg-transparent px-3 py-2 text-sm text-slate-900 outline-none placeholder:text-slate-400" />
                    </div>
                    <x-ui.button class="w-full md:w-auto shrink-0">Temukan Kos</x-ui.button>
                </div>
            </form>

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="text-sm text-slate-500 font-medium mr-2">Populer:</span>
                <a href="{{ route('boarding-houses.search', ['prompt' => 'kos dekat kampus dengan wifi']) }}" class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Dekat kampus</a>
                <a href="{{ route('boarding-houses.search', ['price_max' => 1000000]) }}" class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Di bawah 1 juta</a>
                <a href="{{ route('boarding-houses.search', ['prompt' => 'kos nyaman dan aman']) }}" class="inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Kos nyaman</a>
            </div>
        </div>

        <div class="grid gap-5">
            <div class="relative flex items-center justify-center aspect-video w-full">
                <!-- Decorative background elements -->
                <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 h-64 w-64 rounded-full bg-indigo-200/40 blur-3xl"></div>
                
                <img src="{{ asset('assets/iconjpeg.jpeg') }}" alt="Logo SMART KOST" class="relative z-10 w-full h-full object-contain mix-blend-multiply opacity-90">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-200/50 bg-white p-4 text-center shadow-sm">
                    <div class="text-2xl font-black text-blue-600">{{ $boardingHouses->count() }}+</div>
                    <div class="mt-1 text-xs font-medium text-slate-500">Listing Aktif</div>
                </div>
                <div class="rounded-2xl border border-slate-200/50 bg-white p-4 text-center shadow-sm">
                    <div class="text-2xl font-black text-indigo-600">AI</div>
                    <div class="mt-1 text-xs font-medium text-slate-500">Rekomendasi</div>
                </div>
                <div class="rounded-2xl border border-slate-200/50 bg-white p-4 text-center shadow-sm">
                    <div class="text-2xl font-black text-emerald-600">24/7</div>
                    <div class="mt-1 text-xs font-medium text-slate-500">Dukungan</div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-16 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold tracking-wider uppercase text-blue-600">Rekomendasi Cepat</p>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">Eksplorasi Kos Terbaru</h2>
            </div>
            <x-ui.button href="{{ route('boarding-houses.search') }}" variant="secondary" class="rounded-full px-6">Lihat semua</x-ui.button>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($boardingHouses as $boardingHouse)
                <a href="{{ route('boarding-houses.show', $boardingHouse) }}" class="group block overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100/50">
                    <div class="relative h-56 w-full overflow-hidden bg-slate-100">
                        @if ($boardingHouse->primaryPhoto)
                            <img src="{{ $boardingHouse->primaryPhoto->url() }}" alt="Foto {{ $boardingHouse->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center bg-blue-50 text-sm font-medium text-blue-300">Tanpa Foto</div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                        <div class="absolute left-3 top-3">
                            <x-ui.badge variant="success" class="shadow-sm backdrop-blur-md bg-white/90">{{ $boardingHouse->type->value }}</x-ui.badge>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-900 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ $boardingHouse->name }}</h3>
                                <p class="mt-1.5 text-sm text-slate-500 flex items-center gap-1.5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    <span class="line-clamp-1">{{ $boardingHouse->district }}, {{ $boardingHouse->city }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 flex items-end justify-between border-t border-slate-100 pt-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mulai dari</div>
                            <div class="text-xl font-bold text-blue-600">Rp{{ number_format($boardingHouse->price_monthly, 0, ',', '.') }}<span class="text-xs font-medium text-slate-500">/bln</span></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200/60 border-dashed bg-slate-50 p-12 text-center">
                    <p class="text-sm font-medium text-slate-600">Belum ada listing terverifikasi saat ini.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
