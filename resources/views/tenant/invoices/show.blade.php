<x-layouts.app title="Pembayaran {{ $invoice->number }}">
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <x-ui.card class="p-6">
            <div class="text-sm text-slate-600">Invoice</div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $invoice->number }}</h1>

            <dl class="mt-6 grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-600">Kos</dt>
                    <dd class="font-semibold text-slate-900">{{ $invoice->lease->boardingHouse->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-600">Kamar</dt>
                    <dd class="font-semibold text-slate-900">{{ $invoice->lease->room->room_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-600">Periode</dt>
                    <dd class="font-semibold text-slate-900">{{ $invoice->period_start->format('d M Y') }} - {{ $invoice->period_end->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-600">Jatuh tempo</dt>
                    <dd class="font-semibold text-slate-900">{{ $invoice->due_date->format('d M Y') }}</dd>
                </div>
            </dl>

            <div class="mt-6 rounded-md bg-blue-50 p-4 text-sm text-slate-700">
                Pembayaran online diproses melalui Midtrans Snap. Status tagihan akan diperbarui otomatis setelah notifikasi pembayaran diterima.
            </div>

            @if ($invoice->payments->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-slate-950">Riwayat pembayaran</h2>
                    <div class="mt-3 grid gap-3">
                        @foreach ($invoice->payments->sortByDesc('created_at') as $payment)
                            <div class="rounded-md border border-blue-100 bg-white p-4 text-sm">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-slate-950">{{ ucfirst($payment->provider) }} - {{ $payment->method }}</div>
                                        <div class="mt-1 text-slate-600">{{ $payment->provider_reference }}</div>
                                    </div>
                                    <x-ui.badge variant="primary">{{ $payment->status->value }}</x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.card>

        <aside class="self-start">
            <x-ui.card>
            <div class="mt-8 flex flex-col items-center justify-center border-b border-slate-100 pb-8">
                <div class="text-sm font-semibold uppercase tracking-wider text-slate-500">Total Tagihan</div>
                <div class="mt-2 text-4xl font-bold text-slate-950">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</div>
                @if($invoice->title)
                    <div class="mt-2 font-medium text-slate-700">{{ $invoice->title }}</div>
                @endif
                @if($invoice->description)
                    <div class="mt-1 text-sm text-slate-500 text-center max-w-md">{{ $invoice->description }}</div>
                @endif
            </div>    
            <div class="mt-4 rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">
                    Status: {{ $invoice->status->value }}
                </div>

                @if ($invoice->status !== \App\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Enums\InvoiceStatus::Cancelled)
                    <form method="POST" action="{{ route('tenant.payments.midtrans.store', $invoice) }}" class="mt-4">
                        @csrf
                        <x-ui.button type="submit" class="w-full">Bayar dengan Midtrans</x-ui.button>
                    </form>
                @else
                    <div class="mt-4 rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                        Tagihan sudah selesai.
                    </div>
                @endif
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
