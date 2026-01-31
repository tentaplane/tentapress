<?php

declare(strict_types=1);

namespace TentaPress\System\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class AdminErrorPagesMiddleware
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (HttpExceptionInterface $e) {
            $status = (int) $e->getStatusCode();

            if ($status === 403 && ! $request->expectsJson() && $this->isAdminRequest($request)) {
                return response()
                    ->view('tentapress-admin::errors.403', [
                        'message' => $e->getMessage() ?: 'You do not have permission to access this page.',
                    ], 403);
            }

            throw $e;
        }
    }

    private function isAdminRequest(Request $request): bool
    {
        // Matches /admin and /admin/*
        return $request->is('admin') || $request->is('admin/*');
    }
}
