<?php

namespace App\Http\Middleware;

use App\Support\AdminPortalAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSection
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $user = $request->user();

        abort_unless($user && AdminPortalAccess::isPortalUser($user), 403);
        abort_unless(AdminPortalAccess::canAccessSection($user, $section), 403);

        return $next($request);
    }
}
