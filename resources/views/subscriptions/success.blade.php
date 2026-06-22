<x-layouts.app title="Status Langganan - SMART KOST">
    <div class="mx-auto max-w-2xl">
        <x-ui.card class="p-8 text-center">
            @if ($payment->status === \App\Enums\PaymentStatus::Paid)
                <div class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Pembayaran berhasil</div>
                <h1 class="mt-3 text-3xl font-bold text-slate-950">Langganan Anda sudah aktif</h1>
                <p class="mt-3 text-slate-600">{{ $payment->subscription->name }} aktif sampai {{ $payment->subscription->ends_at->format('d M Y H:i') }}.</p>
            @else
                <div class="text-sm font-semibold uppercase tracking-wide text-amber-600">Status pembayaran</div>
                <h1 class="mt-3 text-3xl font-bold text-slate-950">{{ ucfirst($payment->status->value) }}</h1>
                <p class="mt-3 text-slate-600">Status akan diperbarui otomatis setelah Midtrans menyelesaikan pembayaran.</p>
            @endif

            <div class="mt-6 flex justify-center gap-3">
                <x-ui.button href="{{ route('subscriptions.index') }}">Lihat langganan</x-ui.button>
                <x-ui.button href="{{ route('dashboard') }}" variant="secondary">Kembali ke dashboard</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
