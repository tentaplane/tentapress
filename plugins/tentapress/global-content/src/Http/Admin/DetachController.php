<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TentaPress\GlobalContent\Services\GlobalContentReferenceResolver;

final readonly class DetachController
{
    public function __construct(
        private GlobalContentReferenceResolver $resolver,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'global_content_id' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json([
            'blocks' => $this->resolver->detachBlocks((int) $data['global_content_id']),
        ]);
    }
}
