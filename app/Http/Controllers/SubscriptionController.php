<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Tenant, UserRole::Owner], true),
            403,
        );

        $role = $request->user()->role->value;

        return view('subscriptions.index', [
            'plans' => config("subscriptions.plans.{$role}", []),
            'activeSubscription' => $request->user()->activeSubscription(),
            'trialCredits' => $request->user()->ai_trial_credits_remaining,
            'payments' => $request->user()
                ->subscriptionPayments()
                ->with('subscription')
                ->latest()
                ->paginate(10),
        ]);
    }
}
