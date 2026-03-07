<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use TentaPress\SystemInfo\Models\TpPluginInstall;

final class InstallAttemptDeleteController
{
    public function __invoke(int $installId): JsonResponse
    {
        $attempt = TpPluginInstall::query()->find($installId);
        if (! $attempt instanceof TpPluginInstall) {
            return response()->json([
                'message' => 'Install attempt not found.',
            ], 404);
        }

        $attempt->delete();

        return response()->json([
            'message' => 'Install attempt deleted.',
            'deleted_id' => $installId,
        ]);
    }
}
