<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use TentaPress\Users\Models\TpRole;

final class UpdateController
{
    public function __invoke(Request $request, TpRole $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'capabilities' => ['array'],
            'capabilities.*' => ['string'],
        ]);

        $role->name = (string) $data['name'];
        $role->slug = Str::slug((string) $data['slug']);
        $role->save();

        $caps = array_values(array_filter(array_map(strval(...), $data['capabilities'] ?? [])));
        $role->capabilities()->sync($caps);

        return to_route('tp.roles.edit', ['role' => $role->id])
            ->with('tp_notice_success', 'Role updated.');
    }
}
