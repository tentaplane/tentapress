<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('s', ''));

        $query = TpPage::query()->latest('updated_at');

        if ($status === 'draft' || $status === 'published') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($qq) use ($search): void {
                $qq->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('slug', '%'.$search.'%');
            });
        }

        $pages = $query->paginate(20)->withQueryString();

        return view('tentapress-pages::pages.index', [
            'pages' => $pages,
            'status' => $status,
            'search' => $search,
        ]);
    }
}
