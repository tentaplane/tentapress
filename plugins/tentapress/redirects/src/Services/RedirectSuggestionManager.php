<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use Illuminate\Support\Facades\Auth;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Models\TpRedirectSuggestion;

final readonly class RedirectSuggestionManager
{
    public function __construct(
        private RedirectPathNormalizer $normalizer,
        private RedirectManager $manager,
    ) {
    }

    /**
     * @param array<string,mixed> $meta
     */
    public function stage(
        string $sourcePath,
        string $targetPath,
        int $statusCode = 301,
        string $origin = 'manual',
        array $meta = [],
        ?string $conflictType = null,
    ): TpRedirectSuggestion {
        $sourcePath = $this->normalizer->normalizeSourcePath($sourcePath);
        $targetPath = $this->normalizer->normalizeTargetPath($targetPath);

        return TpRedirectSuggestion::query()->create([
            'source_path' => $sourcePath,
            'target_path' => $targetPath,
            'status_code' => $statusCode,
            'origin' => $origin,
            'state' => 'pending',
            'conflict_type' => $conflictType,
            'meta' => $meta,
        ]);
    }

    public function approve(TpRedirectSuggestion $suggestion): TpRedirect
    {
        $redirect = $this->manager->create([
            'source_path' => (string) $suggestion->source_path,
            'target_path' => (string) $suggestion->target_path,
            'status_code' => (int) $suggestion->status_code,
            'is_enabled' => true,
            'origin' => (string) $suggestion->origin,
            'notes' => 'Approved from suggestion queue.',
        ]);

        $suggestion->state = 'approved';
        $suggestion->decision_by = $this->actorUserId();
        $suggestion->decision_at = now();
        $suggestion->save();

        return $redirect;
    }

    public function reject(TpRedirectSuggestion $suggestion): void
    {
        $suggestion->state = 'rejected';
        $suggestion->decision_by = $this->actorUserId();
        $suggestion->decision_at = now();
        $suggestion->save();
    }

    private function actorUserId(): ?int
    {
        if (! Auth::check() || ! is_object(Auth::user())) {
            return null;
        }

        $id = (int) (Auth::user()->id ?? 0);

        return $id > 0 ? $id : null;
    }
}
