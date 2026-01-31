<?php

declare(strict_types=1);

namespace TentaPress\System\Http;

use Illuminate\Routing\Router;

final readonly class AdminMiddleware
{
    public function __construct(
        private Router $router,
    ) {
    }

    /**
     * Ensure the tp.admin middleware group exists.
     *
     * @param  array<int,string>  $baseMiddleware
     */
    public function ensureGroup(array $baseMiddleware = ['web']): void
    {
        $stack = array_values(array_unique(array_merge($baseMiddleware, [
            'tp.auth',
            'tp.admin.errors',
        ])));

        $this->router->middlewareGroup('tp.admin', $stack);
    }
}
