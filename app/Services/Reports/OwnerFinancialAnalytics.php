<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class OwnerFinancialAnalytics
{
    /**
     * @return array<string, mixed>
     */
    public function forPeriod(User $owner, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $payments = $this->paymentsForPeriod($owner, $periodStart, $periodEnd);
        $invoices = $this->invoicesForPeriod($owner, $periodStart, $periodEnd);
        $totalRevenue = (int) $payments->sum('amount');
        $previousRevenue = $this->revenueForPeriod(
            $owner,
            $periodStart->subMonth()->startOfMonth(),
            $periodStart->subMonth()->endOfMonth(),
        );
        $totalBilled = (int) $invoices
            ->reject(fn (Invoice $invoice): bool => $invoice->status === InvoiceStatus::Cancelled)
            ->sum('amount');
        $outstandingInvoices = $invoices->filter(
            fn (Invoice $invoice): bool => in_array($invoice->status, [InvoiceStatus::Unpaid, InvoiceStatus::Overdue], true),
        );
        $outstandingAmount = (int) $outstandingInvoices->sum('amount');
        $overdueAmount = (int) $outstandingInvoices
            ->filter(fn (Invoice $invoice): bool => $invoice->status === InvoiceStatus::Overdue || $invoice->due_date->lt($periodEnd))
            ->sum('amount');
        $collectionRate = $totalBilled > 0
            ? round(min(100, ($totalRevenue / $totalBilled) * 100), 1)
            : 0.0;
        $activeLeases = Lease::query()
            ->whereBelongsTo($owner, 'owner')
            ->where('status', LeaseStatus::Active->value)
            ->with('room:id,price_monthly')
            ->get();
        $totalRooms = Room::query()
            ->whereHas('boardingHouse', fn ($query) => $query->where('owner_id', $owner->id))
            ->count();
        $activeLeaseCount = $activeLeases->count();
        $occupancyRate = $totalRooms > 0
            ? round(min(100, ($activeLeaseCount / $totalRooms) * 100), 1)
            : 0.0;
        $monthlyRecurringRevenue = (int) $activeLeases->sum(
            fn (Lease $lease): int => $lease->room?->price_monthly ?? 0,
        );
        $effectiveCollectionRate = $totalBilled > 0 ? $collectionRate : 100.0;
        $boardingHouseBreakdown = $this->boardingHouseBreakdown($payments);

        return [
            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
            'previousRevenue' => $previousRevenue,
            'revenueGrowth' => $this->growthPercentage($totalRevenue, $previousRevenue),
            'paidInvoiceCount' => $payments->pluck('invoice_id')->unique()->count(),
            'averagePayment' => (int) round($payments->avg('amount') ?? 0),
            'totalBilled' => $totalBilled,
            'outstandingAmount' => $outstandingAmount,
            'overdueAmount' => $overdueAmount,
            'collectionRate' => $collectionRate,
            'activeLeaseCount' => $activeLeaseCount,
            'totalRooms' => $totalRooms,
            'occupancyRate' => $occupancyRate,
            'monthlyRecurringRevenue' => $monthlyRecurringRevenue,
            'projectedNextMonthRevenue' => (int) round($monthlyRecurringRevenue * ($effectiveCollectionRate / 100)),
            'averageDaysToPay' => $this->averageDaysToPay($payments),
            'boardingHouseBreakdown' => $boardingHouseBreakdown,
            'dailyRevenueTrend' => $this->dailyRevenueTrend($payments, $periodStart, $periodEnd),
            'sixMonthRevenueTrend' => $this->sixMonthRevenueTrend($owner, $periodStart),
            'paymentMethodBreakdown' => $this->paymentMethodBreakdown($payments),
            'agingBuckets' => $this->agingBuckets($outstandingInvoices, $periodEnd),
            'operationalInsights' => $this->operationalInsights(
                $totalRevenue,
                $previousRevenue,
                $overdueAmount,
                $outstandingAmount,
                $occupancyRate,
                $boardingHouseBreakdown,
            ),
        ];
    }

    /**
     * @return Collection<int, Payment>
     */
    private function paymentsForPeriod(User $owner, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Collection
    {
        return Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$periodStart, $periodEnd])
            ->whereHas('invoice.lease', fn ($query) => $query->where('owner_id', $owner->id))
            ->with(['invoice.lease.boardingHouse', 'invoice.lease.room', 'invoice.lease.tenant'])
            ->latest('paid_at')
            ->get();
    }

    /**
     * @return Collection<int, Invoice>
     */
    private function invoicesForPeriod(User $owner, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Collection
    {
        return Invoice::query()
            ->whereHas('lease', fn ($query) => $query->where('owner_id', $owner->id))
            ->whereDate('period_start', '<=', $periodEnd)
            ->whereDate('period_end', '>=', $periodStart)
            ->get();
    }

    private function revenueForPeriod(User $owner, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): int
    {
        return (int) Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$periodStart, $periodEnd])
            ->whereHas('invoice.lease', fn ($query) => $query->where('owner_id', $owner->id))
            ->sum('amount');
    }

    private function growthPercentage(int $currentRevenue, int $previousRevenue): ?float
    {
        if ($previousRevenue === 0) {
            return $currentRevenue === 0 ? 0.0 : null;
        }

        return round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1);
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @return Collection<int, array{name: string, total: int, count: int, share: float}>
     */
    private function boardingHouseBreakdown(Collection $payments): Collection
    {
        $totalRevenue = max(1, (int) $payments->sum('amount'));

        return $payments
            ->groupBy(fn (Payment $payment): string => (string) $payment->invoice->lease->boarding_house_id)
            ->map(fn (Collection $groupedPayments): array => [
                'name' => $groupedPayments->first()->invoice->lease->boardingHouse->name,
                'total' => (int) $groupedPayments->sum('amount'),
                'count' => $groupedPayments->pluck('invoice_id')->unique()->count(),
                'share' => round(((int) $groupedPayments->sum('amount') / $totalRevenue) * 100, 1),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @return Collection<int, array{date: string, label: string, total: int}>
     */
    private function dailyRevenueTrend(
        Collection $payments,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
    ): Collection {
        $dailyTotals = $payments
            ->groupBy(fn (Payment $payment): string => $payment->paid_at->format('Y-m-d'))
            ->map(fn (Collection $dailyPayments): int => (int) $dailyPayments->sum('amount'));
        $days = collect();

        for ($day = $periodStart; $day->lte($periodEnd); $day = $day->addDay()) {
            $days->push([
                'date' => $day->format('Y-m-d'),
                'label' => $day->format('d M'),
                'total' => $dailyTotals->get($day->format('Y-m-d'), 0),
            ]);
        }

        return $days;
    }

    /**
     * @return Collection<int, array{month: string, label: string, total: int}>
     */
    private function sixMonthRevenueTrend(User $owner, CarbonImmutable $selectedMonth): Collection
    {
        $rangeStart = $selectedMonth->subMonths(5)->startOfMonth();
        $rangeEnd = $selectedMonth->endOfMonth();
        $monthlyTotals = Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->whereHas('invoice.lease', fn ($query) => $query->where('owner_id', $owner->id))
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Payment $payment): string => $payment->paid_at->format('Y-m'))
            ->map(fn (Collection $monthlyPayments): int => (int) $monthlyPayments->sum('amount'));

        return collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($selectedMonth, $monthlyTotals): array {
                $month = $selectedMonth->subMonths($monthsAgo);

                return [
                    'month' => $month->format('Y-m'),
                    'label' => $month->translatedFormat('M Y'),
                    'total' => $monthlyTotals->get($month->format('Y-m'), 0),
                ];
            });
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @return Collection<int, array{method: string, total: int, count: int}>
     */
    private function paymentMethodBreakdown(Collection $payments): Collection
    {
        return $payments
            ->groupBy('method')
            ->map(fn (Collection $methodPayments, string $method): array => [
                'method' => $method,
                'total' => (int) $methodPayments->sum('amount'),
                'count' => $methodPayments->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, Invoice>  $outstandingInvoices
     * @return array{not_due: int, days_1_30: int, days_31_60: int, days_over_60: int}
     */
    private function agingBuckets(Collection $outstandingInvoices, CarbonImmutable $periodEnd): array
    {
        $buckets = [
            'not_due' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_over_60' => 0,
        ];

        foreach ($outstandingInvoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays($periodEnd, false);

            if ($daysOverdue <= 0) {
                $buckets['not_due'] += $invoice->amount;
            } elseif ($daysOverdue <= 30) {
                $buckets['days_1_30'] += $invoice->amount;
            } elseif ($daysOverdue <= 60) {
                $buckets['days_31_60'] += $invoice->amount;
            } else {
                $buckets['days_over_60'] += $invoice->amount;
            }
        }

        return $buckets;
    }

    /**
     * @param  Collection<int, Payment>  $payments
     */
    private function averageDaysToPay(Collection $payments): float
    {
        $days = $payments->map(
            fn (Payment $payment): int => (int) $payment->invoice->due_date->diffInDays($payment->paid_at, false),
        );

        return round((float) ($days->avg() ?? 0), 1);
    }

    /**
     * @param  Collection<int, array{name: string, total: int, count: int, share: float}>  $boardingHouseBreakdown
     * @return array<int, string>
     */
    private function operationalInsights(
        int $totalRevenue,
        int $previousRevenue,
        int $overdueAmount,
        int $outstandingAmount,
        float $occupancyRate,
        Collection $boardingHouseBreakdown,
    ): array {
        $insights = [];

        if ($previousRevenue > 0 && $totalRevenue < $previousRevenue) {
            $insights[] = 'Pendapatan turun dibanding bulan lalu. Periksa invoice belum lunas dan unit yang kosong.';
        } elseif ($totalRevenue > $previousRevenue) {
            $insights[] = 'Pendapatan tumbuh dibanding bulan lalu. Pertahankan tingkat penagihan dan okupansi.';
        }

        if ($outstandingAmount > 0 && ($overdueAmount / $outstandingAmount) >= 0.5) {
            $insights[] = 'Lebih dari separuh piutang sudah jatuh tempo. Prioritaskan pengingat dan tindak lanjut pembayaran.';
        }

        if ($occupancyRate < 70) {
            $insights[] = 'Okupansi di bawah 70%. Evaluasi harga, kualitas listing, dan promosi kamar kosong.';
        } elseif ($occupancyRate >= 90) {
            $insights[] = 'Okupansi sangat tinggi. Pertimbangkan penyesuaian harga bertahap atau penambahan kapasitas.';
        }

        if (($boardingHouseBreakdown->first()['share'] ?? 0) >= 60) {
            $insights[] = 'Pendapatan terkonsentrasi pada satu kos. Diversifikasi performa properti lain untuk mengurangi risiko.';
        }

        return $insights === []
            ? ['Kinerja periode ini stabil. Pantau collection rate, okupansi, dan tren pendapatan secara berkala.']
            : $insights;
    }
}
