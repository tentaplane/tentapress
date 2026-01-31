<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Menus\Services\MenuRenderer;
use TentaPress\Menus\Services\ThemeMenuLocations;

final class IndexController
{
    public function __invoke(MenuRenderer $renderer, ThemeMenuLocations $locations): View
    {
        if (! Schema::hasTable('tp_menus')) {
            return view('tentapress-menus::menus.index', [
                'menus' => TpMenu::query()->whereKey(-1)->paginate(20),
                'counts' => [],
                'menuLocations' => [],
                'locationsWithMenus' => $renderer->locationsWithMenus(),
            ]);
        }

        $menus = TpMenu::query()
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $menuIds = $menus->getCollection()->pluck('id')->all();

        $counts = [];
        if ($menuIds !== [] && Schema::hasTable('tp_menu_items')) {
            $counts = TpMenuItem::query()
                ->selectRaw('menu_id, COUNT(*) as aggregate')
                ->whereIn('menu_id', $menuIds)
                ->groupBy('menu_id')
                ->pluck('aggregate', 'menu_id')
                ->map(static fn ($v): int => (int) $v)
                ->all();
        }

        $locationLabels = [];
        foreach ($locations->all() as $loc) {
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
        ]);
    }
}
