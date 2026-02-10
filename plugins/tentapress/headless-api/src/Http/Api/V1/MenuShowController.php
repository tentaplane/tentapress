<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\Menus\Services\MenuRenderer;

final class MenuShowController
{
    public function __invoke(string $location, MenuRenderer $menus): JsonResponse
    {
        $items = $menus->itemsForLocation($location);
        $menu = $menus->menuForLocation($location);

        if (! $menus->hasLocation($location)) {
            return Response::json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Menu location not found',
                ],
            ], 404);
        }

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
