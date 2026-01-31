<?php

declare(strict_types=1);

namespace TentaPress\System\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminAuthMiddleware
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        abort_if($request->expectsJson(), 401);

        if ($request->hasSession()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        // Prefer route name if it exists
        if (function_exists('route') && resolve('router')->has('tp.login')) {
            return to_route('tp.login');
        }

        return redirect()->to('/admin/login');
    }
}
