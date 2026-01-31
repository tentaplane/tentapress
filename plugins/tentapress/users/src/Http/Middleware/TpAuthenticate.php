<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class TpAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        // Preserve intended URL for redirect after login
        $request->session()->put('url.intended', $request->fullUrl());

        return to_route('tp.login');
    }
}
