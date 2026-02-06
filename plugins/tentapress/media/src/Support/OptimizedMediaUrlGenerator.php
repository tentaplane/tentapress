<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Optimization\OptimizationManager;

final readonly class OptimizedMediaUrlGenerator implements MediaUrlGenerator
{
    public function __construct(
        private MediaUrlGenerator $base,
        private OptimizationManager $manager,
    ) {
    }

    public function url(TpMedia $media): ?string
    {
        return $this->base->url($media);
    }

    public function imageUrl(TpMedia $media, array $params = []): ?string
    {
        $url = $this->base->url($media);

        if ($url === null) {
            return null;
        }

        $mime = (string) ($media->mime_type ?? '');
        $isImage = $mime !== '' && str_starts_with($mime, 'image/');

        if (! $isImage) {
            return $this->base->imageUrl($media, $params);
        }

        $optimized = $this->manager->imageUrl($url, $params);

        return $optimized ?? $this->base->imageUrl($media, $params);
    }
}
