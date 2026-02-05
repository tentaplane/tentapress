<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Http\Request;
use TentaPress\Seo\Models\TpSeoPost;

final readonly class SeoPostSaver
{
    public function __construct(private SeoEntitySaver $entitySaver)
    {
    }

    public function syncFromRequest(int $postId, Request $request): void
    {
        $this->entitySaver->syncFromRequest(
            $postId,
            'tp_seo_posts',
            'post_id',
            TpSeoPost::class,
            $request
        );
    }
}
