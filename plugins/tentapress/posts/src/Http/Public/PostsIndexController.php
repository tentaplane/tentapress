<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Public;

use Illuminate\Support\Facades\View;
use TentaPress\Posts\Models\TpPost;

final class PostsIndexController
{
    public function __invoke()
    {
        $posts = TpPost::query()
            ->with('author')
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')->latest()
            ->paginate(12);

        $view = View::exists('tp-theme::posts.index')
            ? 'tp-theme::posts.index'
            : 'tentapress-posts::public.index';

        return view($view, [
            'posts' => $posts,
        ]);
    }
}
