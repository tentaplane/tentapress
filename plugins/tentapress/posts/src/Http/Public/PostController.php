<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Public;

use TentaPress\Posts\Models\TpPost;
use TentaPress\Posts\Services\PostRenderer;

final class PostController
{
    public function __invoke(PostRenderer $renderer, string $slug)
    {
        $post = TpPost::query()
            ->with('author')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->first();

        abort_unless($post?->exists, 404);

        return $renderer->render($post);
    }
}
