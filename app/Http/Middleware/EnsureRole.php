<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            abort(403);
        }

        $allowedRoles = collect($roles)
            ->map(fn (string $role): ?UserRole => UserRole::tryFrom($role))
            ->filter();

        if ($allowedRoles->doesntContain($user->role)) {
            abort(403);
        }

        return $next($request);
    }
}
