<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:  ->middleware('role:admin')
     *                   ->middleware('role:admin,hr')   // any of these roles
     *
     * The "...$roles" parameter collects every role passed after the colon,
     * so a single middleware handles both single- and multi-role checks.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Not logged in at all — send them to the login page.
        // (Normally the 'auth' middleware runs first, but this is a safety net.)
        if (! $user) {
            return redirect()->route('login');
        }

        // Logged in, but the role isn't one of the allowed ones → block.
        if (! $user->hasAnyRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
