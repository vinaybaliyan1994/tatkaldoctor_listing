<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied. Super admin only.'], 403);
        }

        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 403);
        }

        return $next($request);
    }
}
