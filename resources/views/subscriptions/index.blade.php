<x-layouts.app title="Langganan AI - SMART KOST">
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between mb-8">
        <div class="max-w-xl">
            <x-ui.badge variant="primary" class="bg-indigo-100 text-indigo-800 border-indigo-200">💎 SMART KOST Premium</x-ui.badge>
            <h1 class="mt-4 text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Pilih Paket Terbaik Anda</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">Akses fitur eksklusif, wawasan area dengan kecerdasan buatan, dan alat manajemen cerdas khusus untuk {{ auth()->user()->role->value === 'owner' ? 'mitra properti' : 'penyewa' }}.</p>
        </div>

        <x-ui.card class="min-w-64 border-0 bg-gradient-to-br from-indigo-50 to-blue-50 shadow-sm sm:min-w-72">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs font-bold uppercase tracking-widest text-slate-500">Kuota AI Anda</div>
                <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            
            @if ($activeSubscription)
                <div class="mt-2 text-3xl font-black text-emerald-600">
                    {{ $activeSubscription->ai_request_limit === null || $activeSubscription->ai_request_limit === -1 ? 'Unlimited' : $activeSubscription->remainingAiRequests() }}
                </div>
                <div class="mt-1 text-xs font-medium text-slate-500">Aktif sampai {{ $activeSubscription->ends_at->format('d M Y') }}</div>
            @elseif ($trialCredits > 0)
                <div class="mt-2 text-3xl font-black text-indigo-600">{{ $trialCredits }} <span class="text-lg font-bold text-indigo-400">Trial</span></div>
                <div class="mt-1 text-xs font-medium text-slate-500">Gunakan sebelum berlangganan</div>
            @else
                <div class="mt-2 text-xl font-bold text-rose-600">Kuota Habis</div>
                <div class="mt-1 text-xs font-medium text-slate-500">Silakan pilih paket di bawah</div>
            @endif
        </x-ui.card>
    </div>

    @if ($activeSubscription)
        <div class="mb-10 rounded-3xl border border-emerald-200 bg-emerald-50 p-8 text-center max-w-3xl mx-auto shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 mb-4">
                <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Langganan Anda Sedang Aktif</h2>
            <p class="text-slate-600 mb-0 text-base leading-relaxed">
                Anda sedang menikmati fitur eksklusif dari paket <strong class="font-bold text-slate-900">{{ $activeSubscription->name }}</strong>.<br>
                Langganan Anda akan berakhir pada <strong class="font-bold text-slate-900">{{ $activeSubscription->ends_at->format('d F Y') }}</strong>.
            </p>
        </div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2 lg:gap-8 max-w-5xl mx-auto">
        @foreach ($plans as $planCode => $plan)
            <div class="relative flex flex-col rounded-3xl border {{ $activeSubscription?->plan_code === $planCode ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-slate-200' }} bg-white p-8 shadow-xl shadow-slate-200/40 transition-transform duration-300 hover:-translate-y-1">
                @if ($activeSubscription?->plan_code === $planCode)
                    <div class="absolute -top-3.5 left-0 right-0 mx-auto w-fit rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white shadow-sm">Paket Anda Saat Ini</div>
                @elseif ($loop->first)
                    <div class="absolute -top-3.5 left-0 right-0 mx-auto w-fit rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white shadow-sm">Paling Populer</div>
                @endif
                
                <div class="mb-4 text-xs font-bold uppercase tracking-widest text-indigo-600">{{ $plan['duration_days'] }} Hari Akses</div>
                <h2 class="text-2xl font-extrabold text-slate-900">{{ $plan['name'] }}</h2>
                <div class="mt-4 flex items-baseline text-4xl font-black text-slate-900">
                    <span class="text-2xl font-bold text-slate-400 mr-1">Rp</span>{{ number_format($plan['price'], 0, ',', '.') }}
                    <span class="ml-1 text-sm font-medium text-slate-500">/bln</span>
                </div>

                <ul class="mt-8 mb-8 grid gap-4 flex-1">
                    @foreach ($plan['features'] as $feature)
                        <li class="flex items-start gap-3">
                            <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-3 w-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </div>
                            <span class="text-sm font-medium text-slate-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('subscriptions.payments.store', $planCode) }}" class="mt-auto">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl {{ $activeSubscription?->plan_code === $planCode ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 hover:bg-slate-800' }} px-5 py-3.5 text-sm font-bold text-white shadow-lg transition-all hover:shadow-slate-900/20 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 active:scale-[0.98]">
                        {{ $activeSubscription?->plan_code === $planCode ? 'Perpanjang Paket Ini' : 'Pilih Paket Ini' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-16">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Riwayat Pembayaran Langganan</h2>
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Tanggal Transaksi</th>
                            <th class="px-6 py-4">Paket Premium</th>
                            <th class="px-6 py-4">Nominal Tagihan</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payments as $payment)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-medium text-slate-600">{{ $payment->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $payment->subscription?->name ?? $payment->plan_code }}</td>
                                <td class="px-6 py-4 font-medium text-slate-700">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $payment->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $payment->status->value }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm font-medium text-slate-500">Belum ada riwayat pembayaran langganan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
