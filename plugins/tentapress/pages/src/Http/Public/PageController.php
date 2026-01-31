<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Public;

use Illuminate\Http\Request;
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

        abort_unless($page?->exists, 404);

        return $renderer->render($page);
    }
}
