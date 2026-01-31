<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;

final class DestroyController
{
    public function __invoke(TpMenu $menu): RedirectResponse
    {
        $menuId = (int) $menu->id;

        if (Schema::hasTable('tp_menu_items')) {
            TpMenuItem::query()->where('menu_id', $menuId)->delete();
        }

        if (Schema::hasTable('tp_menu_locations')) {
            TpMenuLocation::query()->where('menu_id', $menuId)->delete();
        }

        $menu->delete();

        return to_route('tp.menus.index')
            ->with('tp_notice_success', 'Menu deleted.');
    }
}
