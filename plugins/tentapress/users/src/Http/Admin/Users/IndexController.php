<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use Illuminate\Http\Request;
use TentaPress\Users\Models\TpUser;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('s', ''));

        $query = TpUser::query()
                       ->orderBy('name');

        if ($search !== '') {
            $query->where(static function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('tentapress-users::users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
