<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAnyPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response|RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->route('filament.dashboard.auth.login');
        }

        abort_unless(currentUserHasAnyPermission($permissions), 403);

        return $next($request);
    }
}