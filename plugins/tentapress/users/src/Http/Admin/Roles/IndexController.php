<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Roles;

use Illuminate\Http\Request;
use TentaPress\Users\Models\TpRole;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $query = TpRole::query()
            ->withCount('capabilities')
            ->orderBy('name');

        $search = trim((string) $request->s);

        if ($search !== '') {
            $query->where(static function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        return view('tentapress-users::roles.index', [
            'roles' => $query->get(),
        ]);
    }
}
