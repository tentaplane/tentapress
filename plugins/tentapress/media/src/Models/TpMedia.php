<?php

declare(strict_types=1);

namespace TentaPress\Media\Models;

use Illuminate\Database\Eloquent\Model;

final class TpMedia extends Model
{
    protected $table = 'tp_media';

    protected $fillable = [
        'title',
        'alt_text',
        'caption',
        'source',
        'source_item_id',
        'source_url',
        'license',
        'license_url',
        'attribution',
        'attribution_html',
        'stock_meta',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'source_width',
        'source_height',
        'variants',
        'preview_variant',
        'optimization_status',
        'optimization_error',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'source_width' => 'integer',
        'source_height' => 'integer',
        'variants' => 'array',
        'stock_meta' => 'array',
    ];

    public function variantPath(string $variant): ?string
    {
        $variants = $this->variants;
        if (! is_array($variants) || ! isset($variants[$variant]) || ! is_array($variants[$variant])) {
            return null;
        }

        $path = $variants[$variant]['path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function previewPath(): ?string
    {
        $previewVariant = (string) ($this->preview_variant ?? '');
        if ($previewVariant !== '') {
            $previewPath = $this->variantPath($previewVariant);
            if ($previewPath !== null) {
                return $previewPath;
            }
        }

        $variants = $this->variants;
        if (! is_array($variants)) {
            return null;
        }

        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $path = $variant['path'] ?? null;
            if (is_string($path) && $path !== '') {
                return $path;
            }
        }

        return null;
    }
}
