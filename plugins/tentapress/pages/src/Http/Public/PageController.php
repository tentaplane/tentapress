<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Public;

use Illuminate\Http\RedirectResponse;
use TentaPress\Redirects\Services\RedirectManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Pages\Services\PageRenderer;

final class PageController
{
    public function __invoke(Request $request, PageRenderer $renderer, ?string $slug = null)
    {
        $slug = $slug !== null && $slug !== '' ? $slug : 'home';

        $page = TpPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page?->exists) {
            $fallbackResponse = $this->resolveRedirectFallback($request);
            if ($fallbackResponse instanceof Response || $fallbackResponse instanceof RedirectResponse) {
                return $fallbackResponse;
            }
        }

        abort_unless($page?->exists, 404);

        return $renderer->render($page);
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
