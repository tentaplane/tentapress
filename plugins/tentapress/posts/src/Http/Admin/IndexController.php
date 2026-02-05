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
        $sort = (string) $request->query('sort', 'title');
        $requestedDirection = strtolower((string) $request->query('direction', 'asc'));

        $sortColumns = [
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'status',
            'published' => 'published_at',
            'updated' => 'updated_at',
        ];
        $sort = array_key_exists($sort, $sortColumns) ? $sort : 'updated';

        $defaultDirection = in_array($sort, ['title', 'slug', 'status'], true) ? 'asc' : 'desc';
        $direction = in_array($requestedDirection, ['asc', 'desc'], true) ? $requestedDirection : $defaultDirection;

        $query = TpPost::query()
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('id');

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
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
}
