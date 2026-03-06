<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use Illuminate\Routing\Router;

final readonly class RedirectRouteConflictChecker
{
    public function __construct(
        private Router $router,
    ) {
    }

    public function conflictsWithOwnedRoute(string $sourcePath): bool
    {
        $sourcePath = '/'.ltrim($sourcePath, '/');

        foreach ($this->router->getRoutes() as $route) {
            $uri = (string) $route->uri();
            if ($uri === '' || str_contains($uri, '{')) {
                continue;
            }

            $methods = $route->methods();
            if (! in_array('GET', $methods, true) && ! in_array('HEAD', $methods, true)) {
                continue;
            }

            if ('/'.ltrim($uri, '/') === $sourcePath) {
                return true;
            }
        }

        return false;
    }
}
