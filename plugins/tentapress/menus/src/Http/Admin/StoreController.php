<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use TentaPress\Menus\Http\Requests\StoreMenuRequest;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Services\MenuSlugger;

final class StoreController
{
    public function __invoke(StoreMenuRequest $request, MenuSlugger $slugger): RedirectResponse
    {
        $data = $request->validated();

        $userId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $name = trim((string) ($data['name'] ?? 'Menu'));
        $slugInput = trim((string) ($data['slug'] ?? ''));
        $slug = $slugInput !== '' ? $slugger->unique($slugInput) : $slugger->unique($name);

        $menu = TpMenu::query()->create([
            'name' => $name !== '' ? $name : 'Menu',
            'slug' => $slug,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        return to_route('tp.menus.edit', ['menu' => $menu->id])
            ->with('tp_notice_success', 'Menu created.');
    }
}
