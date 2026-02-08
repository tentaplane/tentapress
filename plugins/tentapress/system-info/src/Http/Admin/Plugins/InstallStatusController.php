<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use TentaPress\System\Support\RuntimeCacheRefresher;
use TentaPress\SystemInfo\Models\TpPluginInstall;

final class InstallStatusController
{
    public function __invoke(int $installId, RuntimeCacheRefresher $runtimeCacheRefresher): JsonResponse
    {
        $attempt = TpPluginInstall::query()->find($installId);
        if (! $attempt instanceof TpPluginInstall) {
            return response()->json([
                'message' => 'Install attempt not found.',
            ], 404);
        }

        if ((string) $attempt->status === 'success') {
            $runtimeCacheRefresher->refreshAfterPluginChange();
        }

        return response()->json([
            'attempt' => [
                'id' => (int) $attempt->id,
                'package' => (string) $attempt->package,
                'status' => (string) $attempt->status,
                'requested_by' => $attempt->requested_by !== null ? (int) $attempt->requested_by : null,
                'output' => (string) ($attempt->output ?? ''),
                'error' => (string) ($attempt->error ?? ''),
                'created_at' => $attempt->created_at?->toIso8601String(),
                'started_at' => $attempt->started_at?->toIso8601String(),
                'finished_at' => $attempt->finished_at?->toIso8601String(),
            ],
        ]);
    }
}
