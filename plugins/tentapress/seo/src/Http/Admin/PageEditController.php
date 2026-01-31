<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class PageEditController
{
    public function __invoke(TpPage $page)
    {
        $seo = TpSeoPage::query()->firstOrNew(['page_id' => (int) $page->id]);

        return view('tentapress-seo::page-edit', [
            'page' => $page,
            'seo' => $seo,
        ]);
    }
}
