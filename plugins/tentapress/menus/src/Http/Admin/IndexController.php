<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Menus\Services\MenuRenderer;
use TentaPress\Menus\Services\ThemeMenuLocations;

final class IndexController
{
    public function __invoke(Request $request, MenuRenderer $renderer, ThemeMenuLocations $locations): View
    {
        $search = trim((string) $request->query('s', ''));
        $sort = in_array((string) $request->query('sort', 'updated'), ['name', 'slug', 'items', 'updated'], true)
            ? (string) $request->query('sort', 'updated')
            : 'updated';
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! Schema::hasTable('tp_menus')) {
            return view('tentapress-menus::menus.index', [
                'menus' => TpMenu::query()->whereKey(-1)->paginate(20),
                'counts' => [],
                'menuLocations' => [],
                'locationsWithMenus' => $renderer->locationsWithMenus(),
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'totalMenus' => 0,
                'totalItems' => 0,
                'assignedLocations' => 0,
                'totalLocations' => count($locations->all()),
            ]);
        }

        $hasMenuItems = Schema::hasTable('tp_menu_items');
        $menusQuery = TpMenu::query();

        if ($search !== '') {
            $menusQuery->where(function ($query) use ($search): void {
                $query->whereLike('name', "%{$search}%")
                    ->orWhereLike('slug', "%{$search}%");
            });
        }

        if ($hasMenuItems) {
            $menusQuery->withCount('items');
        }

        if ($sort === 'items' && $hasMenuItems) {
            $menusQuery->orderBy('items_count', $direction)->orderBy('name');
        } else {
            $sortColumn = match ($sort) {
                'name' => 'name',
                'slug' => 'slug',
                default => 'updated_at',
            };

            $menusQuery->orderBy($sortColumn, $direction);
        }

        $menus = $menusQuery
            ->paginate(20)
            ->withQueryString();

        $menuIds = $menus->getCollection()->pluck('id')->all();

        $counts = [];
        if ($hasMenuItems) {
            foreach ($menus as $menu) {
                $menuId = is_numeric($menu->id) ? (int) $menu->id : 0;
                if ($menuId <= 0) {
                    continue;
                }

                $counts[$menuId] = is_numeric($menu->items_count ?? null) ? (int) $menu->items_count : 0;
            }
        }

        $locationList = $locations->all();
        $locationLabels = [];
        foreach ($locationList as $loc) {
            $key = (string) ($loc['key'] ?? '');
            if ($key === '') {
                continue;
            }
            $locationLabels[$key] = (string) ($loc['label'] ?? $key);
        }

        $menuLocations = [];
        if ($menuIds !== [] && Schema::hasTable('tp_menu_locations')) {
            $rows = TpMenuLocation::query()
                ->whereIn('menu_id', $menuIds)
                ->get(['menu_id', 'location_key']);

            foreach ($rows as $row) {
                $menuId = is_numeric($row->menu_id) ? (int) $row->menu_id : 0;
                $key = trim((string) ($row->location_key ?? ''));
                if ($menuId <= 0 || $key === '') {
                    continue;
                }
                $menuLocations[$menuId] ??= [];
                $menuLocations[$menuId][] = $locationLabels[$key] ?? $key;
            }
        }

        return view('tentapress-menus::menus.index', [
            'menus' => $menus,
            'counts' => $counts,
            'menuLocations' => $menuLocations,
            'locationsWithMenus' => $renderer->locationsWithMenus(),
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'totalMenus' => TpMenu::query()->count(),
            'totalItems' => $hasMenuItems ? TpMenuItem::query()->count() : 0,
            'assignedLocations' => Schema::hasTable('tp_menu_locations')
                ? TpMenuLocation::query()->whereNotNull('menu_id')->count()
                : 0,
            'totalLocations' => count($locationList),
        ]);
    }
}
