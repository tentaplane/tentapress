<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;

final class MediaShowController
{
    public function __invoke(int $id, MediaUrlGenerator $urls, ApiErrorResponder $errors): JsonResponse
    {
        $media = TpMedia::query()->whereKey($id)->first();

        if (! $media) {
            return $errors->notFound('Media not found');
        }

        $url = $urls->url($media);

        return Response::json([
            'data' => [
                'id' => (int) $media->id,
                'title' => $this->nullableString($media->title),
                'alt_text' => $this->nullableString($media->alt_text),
                'caption' => $this->nullableString($media->caption),
                'url' => $url,
                'mime_type' => $this->nullableString($media->mime_type),
                'size' => is_numeric($media->size) ? (int) $media->size : null,
                'width' => is_numeric($media->width) ? (int) $media->width : null,
                'height' => is_numeric($media->height) ? (int) $media->height : null,
                'updated_at' => $media->updated_at?->toIso8601String(),
            ],
        ]);
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
