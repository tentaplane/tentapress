<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use InvalidArgumentException;
use TentaPress\Redirects\Models\TpRedirect;

final readonly class RedirectManager
{
    public function __construct(
        private RedirectPathNormalizer $normalizer,
        private RedirectRouteConflictChecker $routeConflictChecker,
        private RedirectChainValidator $chainValidator,
        private RedirectAuditLogger $auditLogger,
    ) {
    }

    public function match(string $requestPath): ?TpRedirect
    {
        $normalizedPath = $this->normalizer->normalizeSourcePath($requestPath);

        return TpRedirect::query()
            ->enabled()
            ->fromSource($normalizedPath)
            ->first();
    }

    /**
     * @param array{source_path:string,target_path:string,status_code:int,is_enabled:bool,origin?:string,notes?:string|null} $payload
     */
    public function create(array $payload): TpRedirect
    {
        $normalizedPayload = $this->validateAndNormalize($payload);

        $redirect = TpRedirect::query()->create($normalizedPayload);

        $this->auditLogger->record($redirect, 'created', [
            'origin' => $normalizedPayload['origin'],
        ]);

        return $redirect;
    }

    /**
     * @param array{source_path:string,target_path:string,status_code:int,is_enabled:bool,origin?:string,notes?:string|null} $payload
     */
    public function update(TpRedirect $redirect, array $payload): TpRedirect
    {
        $normalizedPayload = $this->validateAndNormalize($payload, (int) $redirect->id);

        $redirect->fill($normalizedPayload);
        $redirect->save();

        $this->auditLogger->record($redirect, 'updated', [
            'origin' => $normalizedPayload['origin'],
        ]);

        return $redirect;
    }

    /**
     * @param array{source_path:string,target_path:string,status_code:int,is_enabled:bool,origin?:string,notes?:string|null} $payload
     * @return array{source_path:string,target_path:string,status_code:int,is_enabled:bool,origin:string,notes:string|null}
     */
    public function validateAndNormalize(array $payload, ?int $ignoreRedirectId = null): array
    {
        $sourcePath = $this->normalizer->normalizeSourcePath((string) ($payload['source_path'] ?? ''));
        $targetPath = $this->normalizer->normalizeTargetPath((string) ($payload['target_path'] ?? ''));

        throw_if($sourcePath === $targetPath, InvalidArgumentException::class, 'Source and target paths cannot be identical.');

        throw_if($this->routeConflictChecker->conflictsWithOwnedRoute($sourcePath), InvalidArgumentException::class, 'Source path conflicts with an owned route.');

        throw_if($this->chainValidator->hasCycle($sourcePath, $targetPath, $ignoreRedirectId), InvalidArgumentException::class, 'Redirect would introduce a loop.');

        $statusCode = (int) ($payload['status_code'] ?? 301);
        throw_unless(in_array($statusCode, [301, 302], true), InvalidArgumentException::class, 'Only 301 and 302 status codes are supported.');

        $origin = trim((string) ($payload['origin'] ?? 'manual'));
        if ($origin === '') {
            $origin = 'manual';
        }

        $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;

        return [
            'source_path' => $sourcePath,
            'target_path' => $targetPath,
            'status_code' => $statusCode,
            'is_enabled' => (bool) ($payload['is_enabled'] ?? true),
            'origin' => $origin,
            'notes' => $notes !== '' ? $notes : null,
        ];
    }
}
