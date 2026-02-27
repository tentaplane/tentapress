<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\BlogBaseResolver;
use TentaPress\Settings\Services\SettingsStore;

final class SiteShowController
{
    public function __invoke(SettingsStore $settings, BlogBaseResolver $blogBaseResolver): JsonResponse
    {
        return Response::json([
            'data' => [
                'site' => [
                    'title' => (string) $settings->get('site.title', ''),
                    'tagline' => (string) $settings->get('site.tagline', ''),
                    'home_page_id' => (int) $settings->get('site.home_page_id', 0),
                    'blog_base' => $blogBaseResolver->fromSettings($settings),
                ],
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
