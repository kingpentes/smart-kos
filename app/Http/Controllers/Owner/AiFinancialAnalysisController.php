<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Ai\ConsumeAiUsage;
use App\Enums\AiFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\FinancialAnalysisRequest;
use App\Models\Invoice;
use App\Services\Ai\GeminiClient;
use App\Services\Reports\OwnerFinancialAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AiFinancialAnalysisController extends Controller
{
    public function __invoke(
        FinancialAnalysisRequest $request,
        OwnerFinancialAnalytics $financialAnalytics,
        GeminiClient $geminiClient,
        ConsumeAiUsage $consumeAiUsage,
    ): JsonResponse {
        $this->authorize('viewAny', Invoice::class);

        if ($request->user()->activeSubscription()?->plan_code !== 'ai_advisor_premium') {
            throw ValidationException::withMessages([
                'ai' => 'Paket Anda tidak mendukung fitur AI Advisor. Silakan upgrade ke paket AI Advisor Premium.',
            ]);
        }

        $selectedMonth = $request->validated('month') ?? now()->format('Y-m');
        $periodStart = CarbonImmutable::createFromFormat('Y-m-d', $selectedMonth.'-01')->startOfMonth();
        $analytics = $financialAnalytics->forPeriod(
            $request->user(),
            $periodStart,
            $periodStart->endOfMonth(),
        );
        $usage = $consumeAiUsage->handle($request->user(), AiFeature::FinancialAnalysis, [
            'period' => $selectedMonth,
        ]);
        $insights = $geminiClient->generateFinancialInsights([
            'period' => $selectedMonth,
            'total_revenue' => $analytics['totalRevenue'],
            'previous_revenue' => $analytics['previousRevenue'],
            'revenue_growth_percent' => $analytics['revenueGrowth'],
            'total_billed' => $analytics['totalBilled'],
            'collection_rate_percent' => $analytics['collectionRate'],
            'outstanding_amount' => $analytics['outstandingAmount'],
            'overdue_amount' => $analytics['overdueAmount'],
            'occupancy_rate_percent' => $analytics['occupancyRate'],
            'monthly_recurring_revenue' => $analytics['monthlyRecurringRevenue'],
            'projected_next_month_revenue' => $analytics['projectedNextMonthRevenue'],
            'average_days_to_pay' => $analytics['averageDaysToPay'],
        ]);

        if ($insights === null) {
            $consumeAiUsage->refund($usage);

            throw ValidationException::withMessages([
                'ai' => 'Analisis AI sedang tidak tersedia. Silakan coba lagi beberapa saat.',
            ]);
        }

        return response()->json([
            'insights' => $insights,
        ]);
    }
}
