<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Http\Request;
use TentaPress\Seo\Models\TpSeoPage;

final class SeoPageSaver
{
    public function __construct(private readonly SeoEntitySaver $entitySaver)
    {
    }

    public function syncFromRequest(int $pageId, Request $request): void
    {
        $this->entitySaver->syncFromRequest(
            $pageId,
            'tp_seo_pages',
            'page_id',
            TpSeoPage::class,
            $request
        );
    }
}
