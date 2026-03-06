<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Public;

use Illuminate\Http\RedirectResponse;
use TentaPress\Redirects\Services\RedirectManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Posts\Services\PostRenderer;

final class PostController
{
    public function __invoke(Request $request, PostRenderer $renderer, string $slug)
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

        if (! $post?->exists) {
            $fallbackResponse = $this->resolveRedirectFallback($request);
            if ($fallbackResponse instanceof Response || $fallbackResponse instanceof RedirectResponse) {
                return $fallbackResponse;
            }
        }

        abort_unless($post?->exists, 404);

        return $renderer->render($post);
    }

    private function resolveRedirectFallback(Request $request): mixed
    {
        $redirectManagerClass = RedirectManager::class;
        if (! class_exists($redirectManagerClass) || ! app()->bound($redirectManagerClass)) {
            return null;
        }

        $redirect = app()->make($redirectManagerClass)->match('/'.ltrim($request->path(), '/'));
        if ($redirect === null) {
            return null;
        }

        return redirect($redirect->target_path, (int) $redirect->status_code);
    }
}
