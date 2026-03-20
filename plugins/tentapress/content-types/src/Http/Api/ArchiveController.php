<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Api;

use Illuminate\Http\JsonResponse;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentTypeApiTransformer;

final class ArchiveController
{
    public function __invoke(string $contentTypeKey, ContentTypeApiTransformer $transformer): JsonResponse
    {
        $contentType = TpContentType::query()
            ->with('fields')
            ->where('key', $contentTypeKey)
            ->where('api_visibility', 'public')
            ->firstOrFail();

        $entries = $contentType->entries()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12);

        return response()->json([
            'data' => $entries->getCollection()->map(fn ($entry): array => $transformer->transformEntry($entry))->all(),
            'type' => $transformer->transformType($contentType),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
        ]);
    }
}
