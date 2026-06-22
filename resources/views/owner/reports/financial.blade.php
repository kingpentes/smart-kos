<x-layouts.app title="Analisa Keuangan">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between mb-8">
        <div>
            <x-ui.badge variant="primary" class="bg-indigo-100 text-indigo-800 border-indigo-200">✨ Owner Finance Intelligence</x-ui.badge>
            <h1 class="mt-4 text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Analisa Keuangan AI</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Laporan pendapatan, piutang, dan wawasan bisnis yang diolah secara otomatis.</p>
        </div>

        <form method="GET" action="{{ route('owner.reports.financial') }}" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-200/60">
            <input type="month" name="month" value="{{ $month }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" onchange="this.form.submit()">
            <x-ui.button class="rounded-xl px-5 shadow-none bg-slate-900 hover:bg-slate-800">Filter</x-ui.button>
        </form>
    </div>

    <!-- Executive Summary Cards -->
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-emerald-300 transition-colors">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Pendapatan Periode Ini</div>
            <div class="relative z-10 text-3xl font-black text-slate-900 tracking-tight">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="relative z-10 mt-4 flex items-center gap-2 text-xs font-semibold">
                @if($revenueGrowth > 0)
                    <span class="flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 border border-emerald-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> +{{ number_format($revenueGrowth, 1) }}%</span>
                    <span class="text-slate-500 font-medium">vs bulan lalu</span>
                @elseif($revenueGrowth < 0)
                    <span class="flex items-center gap-1 rounded-full bg-rose-50 px-2 py-1 text-rose-700 border border-rose-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg> {{ number_format($revenueGrowth, 1) }}%</span>
                    <span class="text-slate-500 font-medium">vs bulan lalu</span>
                @else
                    <span class="text-slate-500 font-medium">Stabil dari bulan lalu</span>
                @endif
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-amber-300 transition-colors">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Piutang / Overdue</div>
            <div class="relative z-10 text-3xl font-black text-slate-900 tracking-tight">Rp{{ number_format($overdueAmount, 0, ',', '.') }}</div>
            <div class="relative z-10 mt-4 text-xs font-medium text-slate-500">Dari total Rp{{ number_format($outstandingAmount, 0, ',', '.') }} belum dibayar</div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-indigo-300 transition-colors">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-indigo-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">MRR Aktif (Estimasi)</div>
                <div class="group relative cursor-help">
                    <svg class="w-4 h-4 text-slate-300 hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="invisible absolute bottom-full left-1/2 mb-2 w-48 -translate-x-1/2 rounded-lg bg-slate-800 p-2 text-center text-[10px] font-medium text-white opacity-0 transition-opacity group-hover:visible group-hover:opacity-100">Monthly Recurring Revenue dari semua sewa aktif saat ini.</div>
                </div>
            </div>
            <div class="relative z-10 text-3xl font-black text-slate-900 tracking-tight">Rp{{ number_format($monthlyRecurringRevenue, 0, ',', '.') }}</div>
            <div class="relative z-10 mt-4 text-xs font-medium text-slate-500">Nilai kontrak sewa yang sedang berjalan</div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm shadow-slate-200/40 relative overflow-hidden group hover:border-blue-300 transition-colors">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-50/50 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Proyeksi Bulan Depan</div>
            <div class="relative z-10 text-3xl font-black text-slate-900 tracking-tight">Rp{{ number_format($projectedNextMonthRevenue, 0, ',', '.') }}</div>
            <div class="relative z-10 mt-4 text-xs font-medium text-slate-500">Diselaraskan dgn Collection Rate {{ number_format($collectionRate, 1) }}%</div>
        </div>
    </div>

    <!-- AI Analysis and Charts -->
    <div class="grid gap-6 lg:grid-cols-[1fr_320px] mb-8">
        <!-- Chart Section -->
        <div class="rounded-3xl border border-slate-200/60 bg-white p-6 sm:p-8 shadow-sm shadow-slate-200/40">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight mb-6">Tren Arus Kas (6 Bulan)</h2>
            <div class="h-[300px] w-full relative">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <!-- AI Insights Sidebar -->
        <div class="rounded-3xl border-2 border-transparent bg-gradient-to-b from-indigo-100 to-white bg-clip-padding p-1 shadow-sm relative overflow-hidden flex flex-col"
             x-data="{
                loading: false,
                loaded: false,
                insights: [],
                error: '',
                async fetchInsights() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await fetch('{{ route('owner.reports.financial.ai') }}?month={{ $month }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Gagal memuat analisis.');
                        this.insights = data.insights || [];
                        this.loaded = true;
                    } catch (error) {
                        this.error = error.message;
                    } finally {
                        this.loading = false;
                    }
                }
             }"
        >
            <div class="bg-white/80 backdrop-blur-xl rounded-[22px] p-6 flex flex-col h-full border border-white/50">
                <div class="mb-5 border-b border-slate-200/60 pb-4">
                    <x-ui.badge variant="primary" class="bg-indigo-100 text-indigo-800 border-indigo-200 mb-3">✨ AI Advisor</x-ui.badge>
                    <h2 class="text-lg font-bold text-slate-900">Rekomendasi Bisnis</h2>
                    <p class="mt-1 text-[11px] font-medium text-slate-500">Biarkan sistem AI kami menganalisa kesehatan kas Anda bulan ini.</p>
                </div>

                <div x-show="!loaded" class="flex-1 flex flex-col items-center justify-center text-center py-4">
                    @if(auth()->user()->activeSubscription()?->plan_code === 'ai_advisor_premium')
                        <button type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition-all focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 disabled:opacity-60" :disabled="loading" @click="fetchInsights()">
                            <span x-show="!loading" class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Dapatkan Insight</span>
                            <span x-show="loading" style="display: none;" class="flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menganalisa...</span>
                        </button>
                    @else
                        <p class="text-xs font-medium text-slate-600 mb-3">Paket Anda tidak mendukung fitur ini. Upgrade ke Paket AI Advisor Premium (Rp150.000) untuk menggunakannya.</p>
                        <x-ui.button href="{{ route('subscriptions.index') }}" class="w-full text-xs py-2">Upgrade Paket</x-ui.button>
                    @endif
                </div>

                <div x-show="loaded" style="display: none;" class="flex-1 flex flex-col gap-3 overflow-y-auto pr-1">
                    <template x-for="(insight, index) in insights" :key="index">
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 text-[11px] font-medium text-slate-700 leading-relaxed shadow-sm">
                            <span class="inline-block w-2 h-2 rounded-full bg-indigo-400 mr-1.5"></span>
                            <span x-text="insight"></span>
                        </div>
                    </template>
                </div>

                <div x-show="error" x-text="error" style="display: none;" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-700"></div>
            </div>
        </div>
    </div>

    <!-- Breakdown details -->
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-5">Analisis Piutang (Aging)</h2>
            <div class="space-y-4">
                @foreach ($agingBuckets as $bucket => $amount)
                    @php
                        $label = match($bucket) {
                            'not_due' => 'Belum Jatuh Tempo',
                            'days_1_30' => 'Telat 1 - 30 Hari',
                            'days_31_60' => 'Telat 31 - 60 Hari',
                            'days_over_60' => 'Telat > 60 Hari',
                        };
                        $color = match($bucket) {
                            'not_due' => 'bg-emerald-500',
                            'days_1_30' => 'bg-amber-400',
                            'days_31_60' => 'bg-rose-500',
                            'days_over_60' => 'bg-rose-700',
                        };
                        $total = array_sum($agingBuckets) ?: 1;
                        $percent = ($amount / $total) * 100;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-600 uppercase tracking-wider">{{ $label }}</span>
                            <span class="text-slate-900">Rp{{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full {{ $color }} rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/60 bg-white shadow-sm shadow-slate-200/40 p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-5">Kontribusi per Properti</h2>
            <div class="space-y-4">
                @forelse ($boardingHouseBreakdown as $data)
                    @php
                        $percent = $totalRevenue > 0 ? ($data['total'] / $totalRevenue) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-600 truncate mr-2">{{ $data['name'] }}</span>
                            <span class="text-slate-900 shrink-0">Rp{{ number_format($data['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm font-medium text-slate-500 py-6 text-center border border-dashed border-slate-200 rounded-2xl">Belum ada pendapatan bulan ini.</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('cashflowChart');
                if (ctx) {
                    const trendData = @json($sixMonthRevenueTrend);
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: trendData.map(item => item.label),
                            datasets: [
                                {
                                    label: 'Pendapatan',
                                    data: trendData.map(item => item.total),
                                    borderColor: '#4f46e5', // indigo-600
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    pointBackgroundColor: '#4f46e5',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        font: { family: "'Instrument Sans', sans-serif", size: 11, weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    titleFont: { family: "'Instrument Sans', sans-serif", size: 13 },
                                    bodyFont: { family: "'Instrument Sans', sans-serif", size: 12 },
                                    padding: 12,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false, drawBorder: false },
                                    ticks: { font: { family: "'Instrument Sans', sans-serif", size: 11 } }
                                },
                                y: {
                                    border: { display: false },
                                    grid: { color: '#f1f5f9', drawBorder: false },
                                    ticks: {
                                        font: { family: "'Instrument Sans', sans-serif", size: 11 },
                                        callback: function(value) {
                                            if(value === 0) return '0';
                                            return 'Rp' + new Intl.NumberFormat('id-ID', { notation: 'compact', compactDisplay: 'short' }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>