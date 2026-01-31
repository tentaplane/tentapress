<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use TentaPress\Users\Models\TpRole;
use TentaPress\Users\Models\TpUser;

final class CreateController
{
    public function __invoke()
    {
        $user = new TpUser([
            'name' => '',
            'email' => '',
            'is_super_admin' => false,
        ]);

        $roles = TpRole::query()->orderBy('name')->get();

        return view('tentapress-users::users.form', [
            'mode' => 'create',
            'user' => $user,
            'roles' => $roles,
            'selected' => [],
        ]);
    }
}
