<?php

namespace App\Http\Middleware;

use App\Support\AdminPortalAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPortal
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        AdminPortalAccess::bootstrapPortalAccess();

        abort_unless(AdminPortalAccess::isPortalUser($request->user()), 403);

        return $next($request);
    }
}
