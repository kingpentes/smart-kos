<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $hasActiveSubscription = Subscription::query()
            ->whereBelongsTo($user)
            ->where('role', $user->role->value)
            ->where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->exists();

        if (! $hasActiveSubscription) {
            return redirect()->route('subscriptions.index')->with('error', 'Anda harus berlangganan terlebih dahulu untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
