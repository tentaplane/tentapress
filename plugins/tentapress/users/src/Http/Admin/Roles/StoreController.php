<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use TentaPress\Users\Models\TpRole;

final class StoreController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'capabilities' => ['array'],
            'capabilities.*' => ['string'],
        ]);

        $name = (string) $data['name'];
        $slugRaw = trim((string) ($data['slug'] ?? ''));

        $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($name);

        $role = TpRole::query()->create([
            'name' => $name,
            'slug' => $slug,
        ]);

        $caps = array_values(array_filter(array_map(strval(...), $data['capabilities'] ?? [])));
        $role->capabilities()->sync($caps);

        return to_route('tp.roles.edit', ['role' => $role->id])
            ->with('tp_notice_success', 'Role created.');
    }
}
