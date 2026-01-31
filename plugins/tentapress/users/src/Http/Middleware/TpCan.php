<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class TpCan
{
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        if (Gate::allows('tp.cap', $capability)) {
            return $next($request);
        }

        abort(403);
    }
}
