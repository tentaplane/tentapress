<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use TentaPress\Menus\Http\Requests\UpdateMenuRequest;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Services\MenuEditorSaver;

final class UpdateController
{
    public function __invoke(UpdateMenuRequest $request, TpMenu $menu, MenuEditorSaver $saver): RedirectResponse
    {
        $data = $request->validated();

        $userId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $saver->updateMenu($menu, $data, $userId);

        return to_route('tp.menus.edit', ['menu' => $menu->id])
            ->with('tp_notice_success', 'Menu updated.');
    }
}
