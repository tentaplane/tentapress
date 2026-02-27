<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Support;

use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Models\TpSeoPost;

final class SeoPayloadBuilder
{
    /**
     * @return array<string,mixed>|null
     */
    public function forPage(?TpSeoPage $seo): ?array
    {
        return $this->build($seo);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function forPost(?TpSeoPost $seo): ?array
    {
        return $this->build($seo);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function build(?object $seo): ?array
    {
        if (! $seo) {
            return null;
        }

        return [
            'title' => $this->nullableString($seo->title ?? null),
            'description' => $this->nullableString($seo->description ?? null),
            'canonical_url' => $this->nullableString($seo->canonical_url ?? null),
            'robots' => $this->nullableString($seo->robots ?? null),
            'og_title' => $this->nullableString($seo->og_title ?? null),
            'og_description' => $this->nullableString($seo->og_description ?? null),
            'og_image' => $this->nullableString($seo->og_image ?? null),
            'twitter_title' => $this->nullableString($seo->twitter_title ?? null),
            'twitter_description' => $this->nullableString($seo->twitter_description ?? null),
            'twitter_image' => $this->nullableString($seo->twitter_image ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
