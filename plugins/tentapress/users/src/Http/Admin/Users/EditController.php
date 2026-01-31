<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use TentaPress\Users\Models\TpRole;
use TentaPress\Users\Models\TpUser;

final class EditController
{
    public function __invoke(TpUser $user)
    {
        $roles = TpRole::query()->orderBy('name')->get();
        $selected = $user->roles()->pluck('tp_roles.id')->all();

        return view('tentapress-users::users.form', [
            'mode' => 'edit',
            'user' => $user,
            'roles' => $roles,
            'selected' => $selected,
        ]);
    }
}
