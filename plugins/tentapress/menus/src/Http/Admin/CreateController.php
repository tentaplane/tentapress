<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\Menus\Models\TpMenu;

final class CreateController
{
    public function __invoke(): View
    {
        $menu = new TpMenu([
            'name' => '',
            'slug' => '',
        ]);

        return view('tentapress-menus::menus.create', [
            'menu' => $menu,
        ]);
    }
}
