<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;
use TentaPress\SystemInfo\Jobs\UpdatePlugins;
use TentaPress\SystemInfo\Models\TpPluginInstall;

final class UpdateController
{
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('tp_plugin_installs')) {
            return $this->errorResponse(
                $request,
                'Plugin install table not found. Run migrations before updating plugins.',
                422
            );
        }

        if (! $this->isSuperAdmin()) {
            return $this->errorResponse(
                $request,
                'Only super administrators can run plugin updates from the admin panel.',
                403
            );
        }

        $allowFullComposerUpdate = filter_var(env('TP_ALLOW_FULL_COMPOSER_UPDATE', false), FILTER_VALIDATE_BOOL);
        $package = $allowFullComposerUpdate
            ? TpPluginInstall::UPDATE_FULL_SENTINEL
            : TpPluginInstall::UPDATE_PLUGINS_SENTINEL;

        try {
            $attempt = TpPluginInstall::query()->create([
                'package' => $package,
                'status' => 'pending',
                'requested_by' => $this->requestedById(),
            ]);

            dispatch(new UpdatePlugins((int) $attempt->id));

            $message = $allowFullComposerUpdate
                ? 'Full Composer update queued.'
                : 'Plugin update queued.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'attempt' => $this->toPayload($attempt->fresh() ?? $attempt),
                ], 202);
            }

            return to_route('tp.plugins.index')->with('tp_notice_success', $message);
        } catch (Throwable $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function toPayload(TpPluginInstall $attempt): array
    {
        return [
            'id' => (int) $attempt->id,
            'package' => $attempt->displayPackage(),
            'status' => (string) $attempt->status,
            'requested_by' => $attempt->requested_by !== null ? (int) $attempt->requested_by : null,
            'output' => (string) ($attempt->output ?? ''),
            'error' => (string) ($attempt->error ?? ''),
            'manual_command' => $attempt->manualCommand(),
            'created_at' => $attempt->created_at?->toIso8601String(),
            'started_at' => $attempt->started_at?->toIso8601String(),
            'finished_at' => $attempt->finished_at?->toIso8601String(),
        ];
    }

    private function requestedById(): ?int
    {
        $id = auth()->id();

        return is_numeric($id) ? (int) $id : null;
    }

    private function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return is_object($user) && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }

    private function errorResponse(Request $request, string $message, int $status): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return to_route('tp.plugins.index')
            ->with('tp_notice_error', $message);
    }
}
