<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Api;

use Illuminate\Http\JsonResponse;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentTypeApiTransformer;

final class IndexController
{
    public function __invoke(ContentTypeApiTransformer $transformer): JsonResponse
    {
        $contentTypes = TpContentType::query()
            ->with('fields')
            ->where('api_visibility', 'public')
            ->orderBy('plural_label')
            ->get();

        return response()->json([
            'data' => $contentTypes->map(fn ($contentType): array => $transformer->transformType($contentType))->all(),
            'meta' => [
                'count' => $contentTypes->count(),
            ],
        ]);
    }
}
