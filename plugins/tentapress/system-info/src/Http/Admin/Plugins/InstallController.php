<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;
use TentaPress\SystemInfo\Http\Requests\InstallPluginRequest;
use TentaPress\SystemInfo\Jobs\InstallPlugin;
use TentaPress\SystemInfo\Models\TpPluginInstall;

final class InstallController
{
    public function __invoke(InstallPluginRequest $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('tp_plugin_installs')) {
            return $this->errorResponse(
                $request,
                'Plugin install table not found. Run migrations before installing plugins.',
                422
            );
        }

        if (! $this->isSuperAdmin()) {
            return $this->errorResponse(
                $request,
                'Only super administrators can install plugins from the admin panel.',
                403
            );
        }

        $package = strtolower(trim((string) $request->validated('package')));

        try {
            $this->assertPackagistPackageExists($package);

            $attempt = TpPluginInstall::query()->create([
                'package' => $package,
                'status' => 'pending',
                'requested_by' => $this->requestedById(),
            ]);

            dispatch(new InstallPlugin((int) $attempt->id));

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Install queued for {$package}.",
                    'attempt' => $this->toPayload($attempt->fresh() ?? $attempt),
                ], 202);
            }

            return to_route('tp.plugins.index')
                ->with('tp_notice_success', "Install queued for {$package}.");
        } catch (Throwable $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }
    }

    private function assertPackagistPackageExists(string $package): void
    {
        $response = Http::acceptJson()
            ->timeout(8)
            ->get("https://repo.packagist.org/p2/{$package}.json");

        throw_unless($response->successful(), RuntimeException::class, 'Package not found on Packagist.');
    }

    /**
     * @return array<string,mixed>
     */
    private function toPayload(TpPluginInstall $attempt): array
    {
        return [
            'id' => (int) $attempt->id,
            'package' => (string) $attempt->package,
            'status' => (string) $attempt->status,
            'requested_by' => $attempt->requested_by !== null ? (int) $attempt->requested_by : null,
            'output' => (string) ($attempt->output ?? ''),
            'error' => (string) ($attempt->error ?? ''),
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

    private function errorResponse(InstallPluginRequest $request, string $message, int $status): RedirectResponse|JsonResponse
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
