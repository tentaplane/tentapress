<?php

declare(strict_types=1);

namespace TentaPress\System\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CanMiddleware
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $capability = trim($capability);
        abort_if($capability === '', 403);

        // Super admin bypass if available
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() === true) {
            return $next($request);
        }

        // Capability check if available
        if (method_exists($user, 'hasCapability') && $user->hasCapability($capability) === true) {
            return $next($request);
        }

        abort(403);
    }
}
