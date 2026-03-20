<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Api;

use Illuminate\Http\JsonResponse;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentTypeApiTransformer;

final class ShowController
{
    public function __invoke(string $contentTypeKey, string $slug, ContentTypeApiTransformer $transformer): JsonResponse
    {
        $contentType = TpContentType::query()
            ->with('fields')
            ->where('key', $contentTypeKey)
            ->where('api_visibility', 'public')
            ->firstOrFail();

        $entry = TpContentEntry::query()
            ->with('contentType.fields')
            ->where('content_type_id', $contentType->id)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $transformer->transformEntry($entry),
            'type' => $transformer->transformType($contentType),
        ]);
    }
}
