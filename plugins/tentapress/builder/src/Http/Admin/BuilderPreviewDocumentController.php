<?php

declare(strict_types=1);

namespace TentaPress\Builder\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use TentaPress\Builder\Support\BuilderPreviewDocumentRenderer;
use TentaPress\Builder\Support\PreviewSnapshotStore;

final readonly class BuilderPreviewDocumentController
{
    public function __construct(
        private PreviewSnapshotStore $snapshots,
        private BuilderPreviewDocumentRenderer $renderer,
    ) {
    }

    public function __invoke(string $token): JsonResponse
    {
        $userId = (int) (Auth::user()?->id ?? 0);
        abort_if($userId <= 0, 403);

        $payload = $this->snapshots->get($token, $userId);
        abort_if(! is_array($payload), 404);

        return response()->json($this->renderer->render($token, $payload));
    }
}
