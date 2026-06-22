<x-layouts.app title="{{ $boardingHouse->name }} - SMART KOST">
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div>
            <div class="flex items-center gap-2 mb-2 text-sm font-semibold text-indigo-600 uppercase tracking-wider">
                <span>{{ $boardingHouse->district }}, {{ $boardingHouse->city }}</span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-500">{{ $boardingHouse->type->value }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">{{ $boardingHouse->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @if ($boardingHouse->trustScore() !== null)
                <div class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700 border border-emerald-100/50 shadow-sm">
                    <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    {{ number_format($boardingHouse->trustScore(), 1) }} Trust Score
                </div>
            @endif
        </div>
    </div>

    <!-- Gallery Bento Grid -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-100 shadow-sm mb-8">
        @if ($boardingHouse->photos->isNotEmpty())
            @php
                $primaryPhoto = $boardingHouse->photos->firstWhere('is_primary', true) ?? $boardingHouse->photos->first();
                $secondaryPhotos = $boardingHouse->photos->reject(fn ($photo) => $photo->is($primaryPhoto))->take(4);
            @endphp

            <div class="grid h-[300px] sm:h-[400px] lg:h-[480px] grid-cols-1 md:grid-cols-[2fr_1fr] gap-2 p-2">
                <div class="relative h-full w-full overflow-hidden rounded-2xl group cursor-pointer">
                    <img src="{{ $primaryPhoto->url() }}" alt="Foto utama {{ $boardingHouse->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-slate-900/10 group-hover:bg-transparent transition-colors"></div>
                </div>

                <div class="hidden md:grid grid-cols-2 grid-rows-2 gap-2 h-full">
                    @forelse ($secondaryPhotos as $index => $photo)
                        <div class="relative h-full w-full overflow-hidden rounded-2xl group cursor-pointer {{ count($secondaryPhotos) === 1 ? 'col-span-2 row-span-2' : '' }} {{ count($secondaryPhotos) === 2 && $index === 0 ? 'col-span-2' : '' }} {{ count($secondaryPhotos) === 3 && $index === 0 ? 'row-span-2' : '' }}">
                            <img src="{{ $photo->url() }}" alt="Foto {{ $boardingHouse->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-slate-900/10 group-hover:bg-transparent transition-colors"></div>
                        </div>
                    @empty
                        <div class="col-span-2 row-span-2 flex items-center justify-center rounded-2xl bg-blue-50/50 text-sm font-medium text-slate-400 border border-dashed border-slate-200">Foto lain belum tersedia</div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="flex h-[300px] lg:h-[400px] items-center justify-center text-sm font-medium text-slate-400">Foto kos belum tersedia</div>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
        <section class="min-w-0 flex flex-col gap-8">
            
            <!-- Description -->
            <div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">Tentang Kos Ini</h2>
                <p class="leading-relaxed text-slate-600">{{ $boardingHouse->description }}</p>
            </div>

            <!-- Facilities -->
            <div>
                <h2 class="text-xl font-bold text-slate-900 mb-4">Fasilitas Utama</h2>
                <div class="flex flex-wrap gap-2.5">
                    @foreach ($boardingHouse->facilities as $facility)
                        <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>{{ $facility->name }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Rules -->
            <div>
                <h2 class="text-xl font-bold text-slate-900 mb-4">Aturan Kos</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($boardingHouse->rules as $rule)
                        <div class="rounded-xl border border-rose-100 bg-rose-50/30 p-4">
                            <div class="font-bold text-slate-900 text-sm mb-1">{{ $rule->key }}</div>
                            <div class="text-sm text-slate-600">{{ $rule->value }}</div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500 text-center">Belum ada aturan tertulis.</div>
                    @endforelse
                </div>
            </div>

            <!-- Location & AI Map -->
            <x-ui.card class="overflow-hidden p-0 border-0 shadow-md">
                <div class="p-6 pb-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Lokasi & Sekitar</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $boardingHouse->address }}</p>
                        </div>
                        @if ($mapData)
                            <x-ui.button href="{{ $mapData['route_url'] }}" target="_blank" variant="secondary" class="bg-white">Buka di Maps</x-ui.button>
                        @endif
                    </div>
                </div>

                @if ($mapData)
                    <div class="relative h-[360px] w-full border-y border-slate-100">
                        <iframe
                            title="Peta lokasi {{ $boardingHouse->name }}"
                            src="{{ $mapData['embed_url'] }}"
                            class="h-full w-full"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                    
                    <div class="p-6 bg-slate-50/50">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Cari Fasilitas Terdekat (Radius 1km)</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($mapData['poi_links'] as $poiLink)
                                <a href="{{ $poiLink['url'] }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    {{ $poiLink['label'] }}
                                    <svg class="w-3 h-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-6 bg-slate-50 text-sm text-center text-slate-500 border-t border-slate-100">Koordinat peta belum tersedia.</div>
                @endif
            </x-ui.card>

            <!-- AI Insight Widget -->
            <div
                class="rounded-3xl border-2 border-transparent bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-1 bg-clip-padding relative overflow-hidden"
                x-data="{
                    loading: false,
                    loaded: false,
                    review: '',
                    score: 0,
                    amenities: {},
                    error: '',
                    async fetchReview() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await fetch('{{ route('api.ai-review', $boardingHouse) }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': @js(csrf_token()),
                                },
                            });
                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Analisis AI gagal dimuat.');
                            }

                            this.review = data.review || '';
                            this.score = data.score || 0;
                            this.amenities = data.amenities || {};
                            this.loaded = true;
                            this.$nextTick(() => this.initChart());
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.loading = false;
                        }
                    },
                    initChart() {
                        const context = document.getElementById('aiRadarChart');
                        if (!context) return;
                        const amenities = Alpine.raw(this.amenities);

                        new Chart(context, {
                            type: 'radar',
                            data: {
                                labels: Object.keys(amenities),
                                datasets: [{
                                    data: Object.values(amenities),
                                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                                    borderColor: 'rgba(79, 70, 229, 1)',
                                    borderWidth: 2,
                                    pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                                    pointBorderColor: '#fff',
                                }],
                            },
                            options: {
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { r: { beginAtZero: true, ticks: { display: false } } },
                            },
                        });
                    },
                }"
            >
                <div class="rounded-[22px] bg-white/60 backdrop-blur-xl p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200/50 pb-5">
                        <div>
                            <x-ui.badge variant="primary" class="bg-indigo-100 text-indigo-800 border-indigo-200">✨ AI Area Insight</x-ui.badge>
                            <h2 class="mt-3 text-xl font-bold text-slate-900">Analisis Komprehensif AI</h2>
                            <p class="mt-1 text-sm text-slate-500">Estimasi kelayakan area & fasilitas umum radius 1km.</p>
                        </div>
                        <div x-show="loaded" style="display: none;" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 shadow-sm">
                            <span class="text-xs font-medium text-indigo-100 uppercase tracking-wide">AI Score</span>
                            <span class="text-xl font-black text-white" x-text="score"></span><span class="text-sm font-medium text-indigo-200">/100</span>
                        </div>
                    </div>

                    @auth
                        <div x-show="!loaded" class="mt-6 text-center py-6">
                            @if ($aiAccess['total_remaining'] > 0 || $aiAccess['total_remaining'] === -1)
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="loading"
                                    @click="fetchReview()"
                                >
                                    <span x-show="!loading" class="flex items-center gap-2"><svg class="w-5 h-5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Jalankan Analisis (1 Kuota)</span>
                                    <span x-show="loading" style="display: none;" class="flex items-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Menganalisis wilayah...</span>
                                </button>
                                <p class="mt-3 text-xs font-medium text-slate-500">Sisa kuota AI Anda: {{ $aiAccess['total_remaining'] === -1 ? 'Unlimited' : $aiAccess['total_remaining'] }}</p>
                            @else
                                <p class="text-sm text-slate-600 mb-4">Kuota AI Anda telah habis. Aktifkan langganan untuk analisis tanpa batas.</p>
                                <x-ui.button href="{{ route('subscriptions.index') }}">Lihat Paket Langganan</x-ui.button>
                            @endif
                        </div>
                    @else
                        <div class="mt-6 text-center py-6 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                            <p class="text-sm font-medium text-slate-600 mb-4">Login untuk melihat analisis rahasia AI mengenai area kos ini.</p>
                            <x-ui.button href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700">Login Sekarang</x-ui.button>
                        </div>
                    @endauth

                    <div x-show="loaded" style="display: none;" class="mt-8 grid items-center gap-8 md:grid-cols-[1fr_1.5fr]">
                        <div class="relative h-56 w-full drop-shadow-sm">
                            <canvas id="aiRadarChart"></canvas>
                        </div>
                        <div class="relative">
                            <svg class="absolute -top-4 -left-4 w-12 h-12 text-indigo-100/50 -z-10" fill="currentColor" viewBox="0 0 32 32"><path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" /></svg>
                            <p class="text-base italic leading-relaxed text-slate-700 font-medium z-10 relative" x-text="review"></p>
                        </div>
                    </div>

                    <div x-show="error" x-text="error" style="display: none;" class="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 border border-rose-100"></div>
                </div>
            </div>

            <!-- Reviews -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Ulasan Terverifikasi</h2>
                    <span class="text-sm font-medium text-slate-500">{{ $boardingHouse->reviews->count() }} Ulasan</span>
                </div>

                <div class="grid gap-4">
                    @forelse ($boardingHouse->reviews->take(5) as $review)
                        <div class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                        {{ substr($review->tenant->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $review->tenant->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 bg-amber-50 px-2 py-1 rounded-md border border-amber-100/50">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    <span class="text-xs font-bold text-amber-700">{{ number_format($review->averageRating(), 1) }}</span>
                                </div>
                            </div>
                            
                            @if ($review->comment)
                                <p class="text-sm text-slate-700 leading-relaxed mb-4">{{ $review->comment }}</p>
                            @endif

                            <div class="flex flex-wrap gap-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">
                                <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">Bersih: {{ $review->cleanliness_rating }}</span>
                                <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">Aman: {{ $review->security_rating }}</span>
                                <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">Sesuai Foto: {{ $review->photo_match_rating }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                            <p class="text-sm font-medium text-slate-500">Belum ada ulasan dari penyewa terverifikasi.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Sticky Booking Sidebar -->
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/40">
                <div class="border-b border-slate-100 pb-5 mb-5">
                    <div class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-1">Harga Sewa</div>
                    <div class="flex items-end gap-1">
                        <span class="text-3xl font-black text-slate-900 tracking-tight">Rp{{ number_format($boardingHouse->price_monthly, 0, ',', '.') }}</span>
                        <span class="text-sm font-medium text-slate-500 mb-1">/bln</span>
                    </div>
                </div>

                <div class="mb-6 rounded-2xl bg-blue-50/50 border border-blue-100 p-4">
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="font-medium text-slate-600">Kamar Tersedia</span>
                        <span class="font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md">{{ $boardingHouse->availableRooms->count() }} Kamar</span>
                    </div>
                    @if($boardingHouse->deposit_amount > 0)
                        <div class="flex justify-between items-center text-sm pt-2 border-t border-blue-100/50">
                            <span class="font-medium text-slate-600">Deposit Awal</span>
                            <span class="font-semibold text-slate-900">Rp{{ number_format($boardingHouse->deposit_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="grid gap-3">
                    @auth
                        @if (auth()->user()->role->value === 'tenant')
                            <x-ui.button href="{{ route('tenant.bookings.create', $boardingHouse) }}" class="w-full bg-blue-600 hover:bg-blue-700 py-3.5 text-base shadow-lg shadow-blue-500/20">Booking Kamar Sekarang</x-ui.button>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 text-center">Login menggunakan akun penyewa untuk melakukan booking.</div>
                        @endif
                    @else
                        <x-ui.button href="{{ route('login') }}" class="w-full bg-slate-900 hover:bg-slate-800 py-3.5 text-base text-white">Login untuk Booking</x-ui.button>
                    @endauth
                </div>

                <div class="mt-6 flex items-center justify-center gap-2 text-xs font-medium text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Pembayaran Aman & Terverifikasi</span>
                </div>
            </div>
        </aside>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</x-layouts.app>
