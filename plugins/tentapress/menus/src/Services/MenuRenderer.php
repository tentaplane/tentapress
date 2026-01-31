<?php

declare(strict_types=1);

namespace TentaPress\Menus\Services;

use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;

final readonly class MenuRenderer
{
    public function __construct(
        private ThemeMenuLocations $locations,
    ) {
    }

    /**
     * @return array<int,array{key:string,label:string,menu_id:int|null,menu_name:string|null,item_count:int}>
     */
    public function locationsWithMenus(): array
    {
        $locations = $this->locations->all();
        if ($locations === [] || ! $this->tablesExist()) {
            return array_map(
                static fn (array $loc): array => [
                    'key' => (string) ($loc['key'] ?? ''),
                    'label' => (string) ($loc['label'] ?? ''),
                    'menu_id' => null,
                    'menu_name' => null,
                    'item_count' => 0,
                ],
                $locations,
            );
        }

        $menuIds = TpMenuLocation::query()
            ->whereIn('location_key', $this->locations->keys())
            ->pluck('menu_id', 'location_key');

        $menus = TpMenu::query()
            ->whereIn('id', array_values(array_filter($menuIds->all())))
            ->get(['id', 'name'])
            ->keyBy('id');

        $counts = TpMenuItem::query()
            ->selectRaw('menu_id, COUNT(*) as aggregate')
            ->whereIn('menu_id', $menus->keys())
            ->groupBy('menu_id')
            ->pluck('aggregate', 'menu_id');

        $out = [];

        foreach ($locations as $loc) {
            $key = (string) ($loc['key'] ?? '');
            $menuId = isset($menuIds[$key]) && is_numeric($menuIds[$key]) ? (int) $menuIds[$key] : null;
            $menu = $menuId !== null ? $menus->get($menuId) : null;
            $itemCount = $menuId !== null && isset($counts[$menuId]) ? (int) $counts[$menuId] : 0;

            $out[] = [
                'key' => $key,
                'label' => (string) ($loc['label'] ?? $key),
                'menu_id' => $menuId,
                'menu_name' => $menu?->name,
                'item_count' => $itemCount,
            ];
        }

        return $out;
    }

    /**
     * @return array<int,array{id:int,title:string,url:string,target:string|null,children:array}>
     */
    public function itemsForLocation(string $locationKey): array
    {
        $menu = $this->menuForLocation($locationKey);
        if (! $menu) {
            return [];
        }

        $items = $menu->items()->get();

        return $this->buildTree($items->all());
    }

    public function hasLocation(string $locationKey): bool
    {
        $locationKey = trim($locationKey);
        if ($locationKey === '') {
            return false;
        }

        return in_array($locationKey, $this->locations->keys(), true);
    }

    public function menuForLocation(string $locationKey): ?TpMenu
    {
        $locationKey = trim($locationKey);
        if ($locationKey === '' || ! $this->hasLocation($locationKey) || ! $this->tablesExist()) {
            return null;
        }

        $location = TpMenuLocation::query()
            ->where('location_key', $locationKey)
            ->first();

        $menuId = $location?->menu_id;
        if (! is_numeric($menuId) || (int) $menuId <= 0) {
            return null;
        }

        return TpMenu::query()->whereKey((int) $menuId)->first();
    }

    /**
     * @param array<int,TpMenuItem> $items
     * @return array<int,array{id:int,title:string,url:string,target:string|null,children:array}>
     */
    private function buildTree(array $items): array
    {
        $byParent = [];

        foreach ($items as $item) {
            $parentId = is_numeric($item->parent_id ?? null) ? (int) $item->parent_id : 0;
            $byParent[$parentId] ??= [];
            $byParent[$parentId][] = $item;
        }

        $make = function (int $parentId) use (&$make, $byParent): array {
            $children = $byParent[$parentId] ?? [];
            $out = [];

            foreach ($children as $child) {
                $id = (int) ($child->id ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $url = trim((string) ($child->url ?? ''));
                if ($url === '') {
                    $url = '#';
                }

                $out[] = [
                    'id' => $id,
                    'title' => trim((string) ($child->title ?? '')),
                    'url' => $url,
                    'target' => $child->target !== null ? (string) $child->target : null,
                    'children' => $make($id),
                ];
            }

            return $out;
        };

        return $make(0);
    }

    private function tablesExist(): bool
    {
        return Schema::hasTable('tp_menus')
            && Schema::hasTable('tp_menu_items')
            && Schema::hasTable('tp_menu_locations');
    }
}
