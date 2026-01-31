<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Posts\Models\TpPost;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('s', ''));

        $query = TpPost::query()->latest('updated_at');

        if ($status === 'draft' || $status === 'published') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($qq) use ($search): void {
                $qq->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('slug', '%'.$search.'%');
            });
        }

        $posts = $query->paginate(20)->withQueryString();

        return view('tentapress-posts::posts.index', [
            'posts' => $posts,
            'status' => $status,
            'search' => $search,
        ]);
    }
}
