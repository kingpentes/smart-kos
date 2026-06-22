<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Ai\AiAccessService;
use App\Services\Reports\OwnerFinancialAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function __invoke(
        Request $request,
        OwnerFinancialAnalytics $financialAnalytics,
        AiAccessService $aiAccessService,
    ): View {
        $this->authorize('viewAny', Invoice::class);

        $selectedMonth = $this->selectedMonth($request);
        $periodStart = CarbonImmutable::createFromFormat('Y-m-d', $selectedMonth.'-01')->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();

        return view('owner.reports.financial', [
            'month' => $selectedMonth,
            ...$financialAnalytics->forPeriod($request->user(), $periodStart, $periodEnd),
            'aiAccess' => $aiAccessService->status($request->user()),
            'financialAiInsights' => $request->session()->get('financial_ai_insights'),
        ]);
    }

    private function selectedMonth(Request $request): string
    {
        $month = $request->string('month')->toString();

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1) {
            return $month;
        }

        return now()->format('Y-m');
    }
}
