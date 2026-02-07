<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use TentaPress\Media\Models\TpMedia;

final readonly class MediaVariantMaintenance
{
    public function __construct(private LocalImageVariantProcessor $processor)
    {
    }

    public function refresh(TpMedia $media): void
    {
        $disk = (string) ($media->disk ?? 'public');
        $path = trim((string) ($media->path ?? ''));

        $processed = $this->processor->process($disk, $path, $media->mime_type);

        $media->fill([
            'size' => $processed['size'] ?? $media->size,
            'width' => $processed['width'],
            'height' => $processed['height'],
            'source_width' => $processed['source_width'],
            'source_height' => $processed['source_height'],
            'variants' => $processed['variants'],
            'preview_variant' => $processed['preview_variant'],
            'optimization_status' => $processed['optimization_status'],
            'optimization_error' => $processed['optimization_error'],
        ]);

        $media->save();
    }
}
