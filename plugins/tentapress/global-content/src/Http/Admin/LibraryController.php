<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use Illuminate\Http\JsonResponse;
use TentaPress\GlobalContent\Services\GlobalContentReferenceResolver;

final readonly class LibraryController
{
    public function __construct(
        private GlobalContentReferenceResolver $resolver,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'entries' => $this->resolver->publishedLibrary(),
        ]);
    }
}
