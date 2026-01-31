<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use TentaPress\Users\Models\TpCapability;

final class CreateController
{
    public function __invoke()
    {
        $caps = TpCapability::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return view('tentapress-users::roles.form', [
            'mode' => 'create',
            'role' => null,
            'capabilities' => $caps,
            'selected' => [],
        ]);
    }
}
