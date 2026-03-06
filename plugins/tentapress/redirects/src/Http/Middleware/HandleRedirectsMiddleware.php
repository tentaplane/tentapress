<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TentaPress\Redirects\Services\RedirectManager;

final readonly class HandleRedirectsMiddleware
{
    public function __construct(
        private RedirectManager $manager,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        foreach (['/admin', '/api', '/storage', '/vendor'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }

        $redirect = $this->manager->match($path);
        if ($redirect === null) {
            return $next($request);
        }

        return redirect($redirect->target_path, (int) $redirect->status_code);
    }
}
