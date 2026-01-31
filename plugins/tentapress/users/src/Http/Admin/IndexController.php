<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin;

use TentaPress\Users\Models\TpUser;

final class IndexController
{
    public function __invoke()
    {
        return view('tentapress-users::users.index', [
            'users' => TpUser::query()->orderBy('id')->paginate(20),
            'currentUser' => auth()->user(),
        ]);
    }
}
