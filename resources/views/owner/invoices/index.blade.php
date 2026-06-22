<x-layouts.dashboard title="Tagihan Penyewa">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tagihan Penyewa</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola dan pantau pembayaran tagihan seluruh penyewa Anda.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('owner.invoices.index') }}" class="flex gap-2">
                <x-ui.select name="status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </x-ui.select>
            </form>
            <x-ui.button href="{{ route('owner.invoices.create') }}">Buat Tagihan Tambahan</x-ui.button>
        </div>
    </div>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Penyewa</th>
                    <th class="px-4 py-3">Kos</th>
                    <th class="px-4 py-3">Jatuh tempo</th>
                    <th class="px-4 py-3">Nominal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-900">{{ $invoice->title ?? 'Tagihan Sewa Kamar' }}</div>
                            <div class="text-xs font-mono text-slate-500 mt-0.5">{{ $invoice->number }}</div>
                        </td>
                        <td class="px-4 py-4 font-medium text-slate-900">{{ $invoice->lease->tenant->name }}</td>
                        <td class="px-4 py-4">
                            <div class="text-slate-900">{{ $invoice->lease->boardingHouse->name }}</div>
                            <div class="text-xs text-slate-500">Kamar {{ $invoice->lease->room->room_number }}</div>
                        </td>
                        <td class="px-4 py-4 text-slate-600">{{ $invoice->due_date->format('d M Y') }}</td>
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
                        <td class="px-4 py-4 text-right">
                            @if ($invoice->status->value !== 'paid')
                                <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" variant="success" class="text-xs px-2 py-1">Tandai Lunas</x-ui.button>
                                </form>
                            @else
                                <span class="text-xs text-slate-500 italic">Lunas</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-ui.card>

    @if($invoices->hasPages())
        <div class="mt-6">{{ $invoices->links() }}</div>
    @endif
</x-layouts.dashboard>
