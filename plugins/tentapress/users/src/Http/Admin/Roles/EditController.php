<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use TentaPress\Users\Models\TpCapability;
use TentaPress\Users\Models\TpRole;

final class EditController
{
    public function __invoke(TpRole $role)
    {
        $caps = TpCapability::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $selected = $role->capabilities()->pluck('key')->all();

        return view('tentapress-users::roles.form', [
            'mode' => 'edit',
            'role' => $role,
            'capabilities' => $caps,
            'selected' => $selected,
        ]);
    }
}
