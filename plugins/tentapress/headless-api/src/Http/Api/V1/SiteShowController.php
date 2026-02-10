<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\Settings\Services\SettingsStore;

final class SiteShowController
{
    public function __invoke(SettingsStore $settings): JsonResponse
    {
        return Response::json([
            'data' => [
                'site' => [
                    'title' => (string) $settings->get('site.title', ''),
                    'tagline' => (string) $settings->get('site.tagline', ''),
                    'home_page_id' => (int) $settings->get('site.home_page_id', 0),
                    'blog_base' => (string) $settings->get('site.blog_base', 'blog'),
                ],
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
