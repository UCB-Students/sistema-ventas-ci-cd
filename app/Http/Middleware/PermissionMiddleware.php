<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions  One or more permission slugs
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check if the user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($request->user()->tienePermiso($permission)) {
                return $next($request);
            }
        }

        // If no permission is found, return 403 Forbidden
        return response()->json(['message' => 'Unauthorized. You do not have the required permissions.'], 403);
    }
}
