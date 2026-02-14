<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Support\MediaVariantMaintenance;

final readonly class RebuildVariantsController
{
    public function __construct(private MediaVariantMaintenance $maintenance)
    {
    }

    public function __invoke(Request $request, TpMedia $media): RedirectResponse
    {
        $variant = trim((string) $request->string('variant')->value());

        if (! str_starts_with((string) ($media->mime_type ?? ''), 'image/')) {
            return to_route('tp.media.edit', ['media' => $media->id])
                ->with('tp_notice_warning', 'Variants are only available for image media.');
        }

        $this->maintenance->refresh($media);
        $media->refresh();

        if ($variant === '') {
            return to_route('tp.media.edit', ['media' => $media->id])
                ->with('tp_notice_success', 'Image variants rebuilt.');
        }

        $variants = is_array($media->variants) ? $media->variants : [];
        if (! isset($variants[$variant]) || ! is_array($variants[$variant])) {
            return to_route('tp.media.edit', ['media' => $media->id])
                ->with('tp_notice_warning', "Variant '{$variant}' is not available for this image.");
        }

        return to_route('tp.media.edit', ['media' => $media->id])
            ->with('tp_notice_success', "Variant '{$variant}' rebuilt.");
    }
}
