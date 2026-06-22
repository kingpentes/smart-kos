<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\CreateSubscriptionPayment;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionPaymentController extends Controller
{
    public function store(
        Request $request,
        string $planCode,
        CreateSubscriptionPayment $createSubscriptionPayment,
    ): RedirectResponse {
        abort_unless(
            in_array($request->user()->role, [UserRole::Tenant, UserRole::Owner], true),
            403,
        );

        $role = $request->user()->role->value;
        /** @var array{name: string, price: int, duration_days: int, ai_request_limit: int, features: array<int, string>}|null $plan */
        $plan = config("subscriptions.plans.{$role}.{$planCode}");

        if ($plan === null) {
            throw ValidationException::withMessages([
                'plan' => 'Paket langganan tidak tersedia untuk akun Anda.',
            ]);
        }

        $payment = $createSubscriptionPayment->handle($request->user(), $planCode, $plan);

        return redirect()->away((string) $payment->raw_payload['redirect_url']);
    }
}
