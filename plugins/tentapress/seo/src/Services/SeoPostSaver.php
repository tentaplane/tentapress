<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use TentaPress\Seo\Models\TpSeoPost;

final class SeoPostSaver
{
    public function syncFromRequest(int $postId, Request $request): void
    {
        if ($postId <= 0) {
            return;
        }

        if (!Schema::hasTable('tp_seo_posts')) {
            return;
        }

        $payload = [
            'post_id' => $postId,
            'title' => $this->nullIfEmpty($request->input('seo_title')),
            'description' => $this->nullIfEmpty($request->input('seo_description')),
            'robots' => $this->nullIfEmpty($request->input('seo_robots')),
            'canonical_url' => $this->nullIfEmpty($request->input('seo_canonical_url')),
            'og_image' => $this->nullIfEmpty($request->input('seo_og_image')),
            'twitter_image' => $this->nullIfEmpty($request->input('seo_twitter_image')),
        ];

        if (!$this->requestHadAnySeoFields($request)) {
            return;
        }

        if ($this->allEmpty($payload)) {
            TpSeoPost::query()->where('post_id', $postId)->delete();
            return;
        }

        TpSeoPost::query()->updateOrCreate(
            ['post_id' => $postId],
            $payload
        );
    }

    private function requestHadAnySeoFields(Request $request): bool
    {
        $keys = [
            'seo_title',
            'seo_description',
            'seo_robots',
            'seo_canonical_url',
            'seo_og_image',
            'seo_twitter_image',
        ];

        foreach ($keys as $k) {
            if ($request->has($k)) {
                return true;
            }
        }

        return false;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $v = trim((string) ($value ?? ''));
        return $v === '' ? null : $v;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function allEmpty(array $payload): bool
    {
        foreach (['title', 'description', 'robots', 'canonical_url', 'og_image', 'twitter_image'] as $k) {
            $v = $payload[$k] ?? null;
            if (is_string($v) && trim($v) !== '') {
                return false;
            }
        }

        return true;
    }
}
