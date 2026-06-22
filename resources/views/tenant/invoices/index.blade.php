<x-layouts.dashboard title="Riwayat Tagihan">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Tagihan</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau seluruh invoice dan status pembayaran Anda.</p>
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">TANGGAL</th>
                        <th class="px-4 py-3 font-semibold">INVOICE</th>
                        <th class="px-4 py-3 font-semibold">KOS</th>
                        <th class="px-4 py-3 font-semibold">TOTAL BAYAR</th>
                        <th class="px-4 py-3 font-semibold">STATUS</th>
                        <th class="px-4 py-3 font-semibold">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-slate-600">{{ $invoice->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-4 font-mono text-sm text-slate-700">
                                {{ $invoice->number }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $invoice->title ?? 'Tagihan Sewa Kamar' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $invoice->lease->boardingHouse->name }} - Kamar {{ $invoice->lease->room->room_number }}</div>
                            </td>
                            <td class="px-4 py-4 font-bold text-slate-900">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                @if($invoice->status->value === 'paid')
                                    <x-ui.badge variant="success">Lunas</x-ui.badge>
                                @elseif($invoice->status->value === 'unpaid')
                                    <x-ui.badge variant="warning">Belum Lunas</x-ui.badge>
                                @elseif($invoice->status->value === 'cancelled')
                                    <x-ui.badge variant="danger">Dibatalkan</x-ui.badge>
                                @else
                                    <x-ui.badge>{{ $invoice->status->value }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <x-ui.button href="{{ route('tenant.invoices.show', $invoice) }}" variant="secondary" class="text-xs px-2 py-1">
                                    Detail
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Belum ada riwayat tagihan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.dashboard>
