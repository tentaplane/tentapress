<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;

final class LocalMediaUrlGenerator implements MediaUrlGenerator
{
    public function url(TpMedia $media): ?string
    {
        $disk = (string) ($media->disk ?? 'public');
        $path = trim((string) ($media->path ?? ''));

        if ($path === '') {
            return null;
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function imageUrl(TpMedia $media, array $params = []): ?string
    {
        $variant = isset($params['variant']) ? (string) $params['variant'] : '';
        if ($variant !== '') {
            $variantPath = $media->variantPath($variant);
            if ($variantPath !== null) {
                $media = clone $media;
                $media->path = $variantPath;
            }

            unset($params['variant']);
        }

        $url = $this->url($media);

        if ($url === null || $params === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($params);
    }
}
