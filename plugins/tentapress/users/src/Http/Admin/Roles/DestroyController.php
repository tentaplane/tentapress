<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use TentaPress\Users\Models\TpRole;

final class DestroyController
{
    public function __invoke(TpRole $role)
    {
        $role->capabilities()->detach();
        $role->delete();

        return to_route('tp.roles.index')
            ->with('tp_notice_success', 'Role deleted.');
    }
}
