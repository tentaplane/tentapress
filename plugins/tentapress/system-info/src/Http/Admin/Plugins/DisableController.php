<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Throwable;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\System\Support\RuntimeCacheRefresher;
use TentaPress\SystemInfo\Http\Requests\PluginActionRequest;

final class DisableController
{
    public function __invoke(PluginActionRequest $request, PluginRegistry $registry, RuntimeCacheRefresher $runtimeCacheRefresher): RedirectResponse|JsonResponse
    {
        $id = (string) $request->validated('id');

        try {
            $registry->disable($id);
            $registry->writeCache();
            $runtimeCacheRefresher->refreshAfterPluginChange();

            if ($request->expectsJson()) {
                return response()->json([
                    'id' => $id,
                    'enabled' => false,
                    'message' => "Disabled {$id}.",
                ]);
            }

            return to_route('tp.plugins.index')
                ->with('tp_notice_success', "Disabled {$id}.");
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'id' => $id,
                    'enabled' => true,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return to_route('tp.plugins.index')
                ->with('tp_notice_error', $e->getMessage());
        }
    }
}
