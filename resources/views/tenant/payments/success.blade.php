<x-layouts.app title="Status Pembayaran">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden text-center p-8">
            @if($payment->status->value === 'paid')
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-6">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Pembayaran Berhasil!</h1>
                <p class="text-slate-600 mb-6">Terima kasih, tagihan #{{ $invoice->number }} Anda telah lunas.</p>
            @elseif($payment->status->value === 'pending')
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 mb-6">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Menunggu Pembayaran</h1>
                <p class="text-slate-600 mb-6">Harap segera selesaikan pembayaran untuk tagihan #{{ $invoice->number }}.</p>
            @else
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-6">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Pembayaran Gagal</h1>
                <p class="text-slate-600 mb-6">Pembayaran Anda tidak dapat diproses. Silakan coba metode pembayaran lain.</p>
            @endif

            <div class="bg-slate-50 p-4 rounded-md inline-block text-left mb-8 w-full max-w-sm">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-slate-500">Nomor Tagihan</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $invoice->number }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-slate-500">Jumlah</span>
                    <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Metode</span>
                    <span class="text-sm font-semibold text-slate-900">{{ strtoupper($payment->method ?? 'MIDTRANS') }}</span>
                </div>
            </div>

            <div>
                <a href="{{ route('tenant.invoices.show', $invoice) }}" class="inline-block rounded-md bg-[#061C5D] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#051543]">Lihat Tagihan</a>
                <a href="{{ route('tenant.dashboard') }}" class="inline-block rounded-md bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm border border-slate-300 ml-3 hover:bg-slate-50">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</x-layouts.app>
