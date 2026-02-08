<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        $package = trim((string) $request->validated('package'));

        try {
            $package = $this->normalizePackageInput($package);

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

    private function normalizePackageInput(string $input): string
    {
        $value = trim($input);
        throw_if($value === '', RuntimeException::class, 'Package is required.');

        if (preg_match('/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/i', $value) === 1) {
            return strtolower($value);
        }

        $rawUrl = $value;
        if (
            ! str_contains($rawUrl, '://')
            && (str_starts_with(strtolower($rawUrl), 'github.com/') || str_starts_with(strtolower($rawUrl), 'packagist.org/'))
        ) {
            $rawUrl = 'https://' . $rawUrl;
        }

        $parts = parse_url($rawUrl);
        throw_if(! is_array($parts), RuntimeException::class, 'Invalid package format.');

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === 'github.com' || $host === 'www.github.com') {
            return $this->normalizeFromGithubParts($parts);
        }

        if ($host === 'packagist.org' || $host === 'www.packagist.org') {
            return $this->normalizeFromPackagistParts($parts);
        }

        throw new RuntimeException('Use vendor/package, a GitHub URL, or a Packagist package URL.');
    }

    /**
     * @param  array<string,mixed>  $parts
     */
    private function normalizeFromGithubParts(array $parts): string
    {
        throw_if(isset($parts['query']) || isset($parts['fragment']), RuntimeException::class, 'GitHub URL cannot include query or fragment.');

        $path = trim((string) ($parts['path'] ?? ''), '/');
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
        throw_if(count($segments) !== 2, RuntimeException::class, 'GitHub URL must be in the form github.com/vendor/package.');

        $owner = $segments[0];
        $repo = preg_replace('/\.git$/i', '', $segments[1]) ?? $segments[1];

        return $this->validatePackageName(strtolower("{$owner}/{$repo}"), 'GitHub URL owner/repo is invalid.');
    }

    /**
     * @param  array<string,mixed>  $parts
     */
    private function normalizeFromPackagistParts(array $parts): string
    {
        throw_if(isset($parts['query']) || isset($parts['fragment']), RuntimeException::class, 'Packagist URL cannot include query or fragment.');

        $path = trim((string) ($parts['path'] ?? ''), '/');
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
        throw_if(
            count($segments) !== 3 || strtolower($segments[0]) !== 'packages',
            RuntimeException::class,
            'Packagist URL must be in the form packagist.org/packages/vendor/package.'
        );

        return $this->validatePackageName(strtolower($segments[1] . '/' . $segments[2]), 'Packagist URL vendor/package is invalid.');
    }

    private function validatePackageName(string $package, string $error): string
    {
        throw_if(preg_match('/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/', $package) !== 1, RuntimeException::class, $error);

        return $package;
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
