<x-layouts.app title="Cari Kos - SMART KOST">
    <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="grid gap-6">
                <!-- AI Finder Card -->
                <div class="rounded-3xl border border-transparent bg-gradient-to-b from-blue-50 to-indigo-50 p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-blue-200/40 blur-2xl"></div>
                    <div class="relative z-10">
                        <x-ui.badge variant="primary" class="bg-white/80 backdrop-blur-md shadow-sm border-blue-100">✨ AI Finder</x-ui.badge>
                        <h1 class="mt-4 text-2xl font-extrabold text-slate-900 tracking-tight">Cari dengan AI</h1>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Ceritakan kos idaman Anda: lokasi, budget, atau fasilitas spesifik.</p>

                        @auth
                            <form method="POST" action="{{ route('ai.boarding-houses.search') }}" class="mt-5 grid gap-4">
                                @csrf
                                <x-ui.label>
                                    <span class="sr-only">Prompt pencarian</span>
                                    <x-ui.textarea name="prompt" rows="3" class="resize-none shadow-sm focus:ring-indigo-500/20 focus:border-indigo-500 text-sm" placeholder="Contoh: Kos putri dekat kampus UGM, harga di bawah 1.5 juta, ada WiFi kencang dan AC">{{ old('prompt', $filters['prompt'] ?? '') }}</x-ui.textarea>
                                    <x-ui.error :messages="$errors->get('prompt')" />
                                </x-ui.label>

                                <div class="flex items-center justify-between rounded-xl bg-white/60 px-3 py-2.5 text-xs font-medium text-slate-700 border border-white">
                                    <span>Sisa kuota AI:</span>
                                    <span class="font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-md">
                                        {{ $aiAccess['total_remaining'] === -1 ? 'Unlimited' : $aiAccess['total_remaining'] }}
                                        @if ($aiAccess['trial_credits'] > 0 && $aiAccess['total_remaining'] !== -1)
                                            <span class="font-normal">({{ $aiAccess['trial_credits'] }} gratis)</span>
                                        @endif
                                    </span>
                                </div>

                                @if ($aiAccess['total_remaining'] > 0 || $aiAccess['total_remaining'] === -1)
                                    <x-ui.button class="w-full bg-indigo-600 hover:bg-indigo-700 hover:shadow-indigo-500/20 focus:ring-indigo-500">Gunakan AI (1 kuota)</x-ui.button>
                                @else
                                    <x-ui.button href="{{ route('subscriptions.index') }}" variant="secondary" class="w-full bg-white">Aktifkan Kuota AI</x-ui.button>
                                @endif
                            </form>
                        @else
                            <x-ui.button href="{{ route('login') }}" class="mt-5 w-full bg-slate-900 hover:bg-slate-800 text-white">Login untuk memakai AI</x-ui.button>
                        @endauth
                    </div>
                </div>

                <!-- Manual Filter -->
                <form method="GET" action="{{ route('boarding-houses.search') }}">
                    <x-ui.card class="grid gap-5">
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-900">Filter Manual</h2>
                            <p class="mt-1 text-xs text-slate-500">Pencarian presisi tanpa kuota AI.</p>
                        </div>

                        <x-ui.label class="grid gap-1.5">
                            <span class="text-sm font-semibold text-slate-700">Lokasi / Nama Daerah</span>
                            <x-ui.input name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Ketik area..." />
                        </x-ui.label>

                        <x-ui.label class="grid gap-1.5">
                            <span class="text-sm font-semibold text-slate-700">Tipe Penghuni</span>
                            <x-ui.select name="type">
                                <option value="">Semua Tipe</option>
                                <option value="male" @selected(($filters['type'] ?? '') === 'male')>Putra</option>
                                <option value="female" @selected(($filters['type'] ?? '') === 'female')>Putri</option>
                                <option value="mixed" @selected(($filters['type'] ?? '') === 'mixed')>Campur</option>
                            </x-ui.select>
                        </x-ui.label>

                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.label class="grid gap-1.5">
                                <span class="text-sm font-semibold text-slate-700">Harga Min</span>
                                <x-ui.input name="price_min" type="number" placeholder="Rp 0" value="{{ $filters['price_min'] ?? '' }}" />
                            </x-ui.label>
                            <x-ui.label class="grid gap-1.5">
                                <span class="text-sm font-semibold text-slate-700">Harga Max</span>
                                <x-ui.input name="price_max" type="number" placeholder="Rp 0" value="{{ $filters['price_max'] ?? '' }}" />
                            </x-ui.label>
                        </div>

                        <div class="grid gap-2.5">
                            <span class="text-sm font-semibold text-slate-700 border-t border-slate-100 pt-4">Fasilitas Tersedia</span>
                            <div class="grid gap-2">
                                @foreach ($facilities as $facility)
                                    <label class="group flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:border-blue-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 has-[:checked]:text-blue-900">
                                        <span>{{ $facility->name }}</span>
                                        <input name="facilities[]" type="checkbox" value="{{ $facility->id }}" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20" @checked(in_array((string) $facility->id, (array) ($filters['facilities'] ?? []), true))>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2">
                            <x-ui.button class="w-full">Terapkan Filter</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            </div>
        </aside>

        <section class="min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Eksplorasi Kos</h2>
                    @if ($recommendationCriteria !== null)
                        <div class="mt-2 text-sm text-slate-600 flex items-center gap-2">
                            @if (($recommendationCriteria['source'] ?? '') === 'failed')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> AI Finder gagal memuat hasil</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 border border-indigo-100"><span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse"></span> Hasil rekomendasi cerdas AI</span>
                            @endif
                        </div>
                    @else
                        <p class="mt-1 text-sm text-slate-500">Menampilkan {{ $boardingHouses->total() }} kos terverifikasi.</p>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                @forelse ($boardingHouses as $boardingHouse)
                    <a href="{{ route('boarding-houses.show', $boardingHouse) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100/50">
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100">
                            @if ($boardingHouse->primaryPhoto)
                                <img src="{{ $boardingHouse->primaryPhoto->url() }}" alt="Foto {{ $boardingHouse->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center bg-blue-50/50 text-sm font-medium text-blue-300">Tanpa Foto</div>
                            @endif
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            
                            <div class="absolute left-3 top-3 flex flex-col gap-2 items-start">
                                <x-ui.badge variant="success" class="shadow-sm backdrop-blur-md bg-white/90">{{ $boardingHouse->type->value }}</x-ui.badge>
                                @if ($boardingHouse->getAttribute('recommendation_score') !== null)
                                    <span class="inline-flex items-center rounded-full bg-indigo-600/90 backdrop-blur-md px-2.5 py-1 text-xs font-bold tracking-wide text-white shadow-sm border border-white/20">
                                        <svg class="w-3 h-3 mr-1 text-indigo-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        Skor AI: {{ $boardingHouse->getAttribute('recommendation_score') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-2 flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                <span class="line-clamp-1">{{ $boardingHouse->district }}, {{ $boardingHouse->city }}</span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-slate-900 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ $boardingHouse->name }}</h3>
                            
                            @if ($boardingHouse->getAttribute('recommendation_breakdown') !== null && $boardingHouse->getAttribute('recommendation_breakdown') !== [])
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($boardingHouse->getAttribute('recommendation_breakdown') as $reason)
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 border border-indigo-100/50">{{ $reason }}</span>
                                    @endforeach
                                </div>
                            @elseif($boardingHouse->facilities->count() > 0)
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($boardingHouse->facilities->take(3) as $facility)
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $facility->name }}</span>
                                    @endforeach
                                    @if($boardingHouse->facilities->count() > 3)
                                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-400">+{{ $boardingHouse->facilities->count() - 3 }} lainnya</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-auto pt-5 border-t border-slate-100 flex items-end justify-between">
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Harga Sewa</div>
                                <div class="text-xl font-black text-blue-600">Rp{{ number_format($boardingHouse->price_monthly, 0, ',', '.') }}<span class="text-xs font-medium text-slate-500">/bln</span></div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center rounded-3xl border border-slate-200 border-dashed bg-slate-50 p-12 text-center">
                        <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Kos Tidak Ditemukan</h3>
                        <p class="mt-1 text-sm text-slate-500 max-w-sm">Coba ubah kriteria pencarian, lokasi, atau gunakan prompt AI yang lebih umum.</p>
                        <x-ui.button href="{{ route('boarding-houses.search') }}" variant="secondary" class="mt-6">Reset Filter</x-ui.button>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $boardingHouses->links() }}
            </div>
        </section>
    </div>
</x-layouts.app>
