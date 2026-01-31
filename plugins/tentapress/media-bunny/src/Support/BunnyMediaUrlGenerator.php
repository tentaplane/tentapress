<?php

declare(strict_types=1);

namespace TentaPress\MediaBunny\Support;

use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;

final class BunnyMediaUrlGenerator implements MediaUrlGenerator
{
    public function url(TpMedia $media): ?string
    {
        $baseUrl = $this->baseUrl();
        $path = $this->path($media);

        if ($baseUrl === '' || $path === '') {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function imageUrl(TpMedia $media, array $params = []): ?string
    {
        $url = $this->url($media);

        if ($url === null) {
            return null;
        }

        $params = array_merge($this->defaultParams(), $params);

        if ($params === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($params);
    }

    /**
     * @return array<string, scalar>
     */
    private function defaultParams(): array
    {
        $params = config('tentapress.media.bunny.default_params', []);

        return is_array($params) ? $params : [];
    }

    private function baseUrl(): string
    {
        $baseUrl = (string) config('tentapress.media.bunny.base_url', '');

        return rtrim($baseUrl, '/');
    }

    private function path(TpMedia $media): string
    {
        return trim((string) ($media->path ?? ''), '/');
    }
}
