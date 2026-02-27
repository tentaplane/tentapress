<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;
use TentaPress\Menus\Services\MenuRenderer;

final class MenuShowController
{
    public function __invoke(string $location, MenuRenderer $menus, ApiErrorResponder $errors): JsonResponse
    {
        if (! $menus->hasLocation($location)) {
            return $errors->notFound('Menu location not found');
        }

        $items = $menus->itemsForLocation($location);
        $menu = $menus->menuForLocation($location);

        return Response::json([
            'data' => [
                'location' => $location,
                'menu' => [
                    'id' => $menu?->id !== null ? (int) $menu->id : null,
                    'name' => $menu?->name !== null ? (string) $menu->name : null,
                    'items' => $items,
                ],
            ],
        ]);
    }
}
