<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use TentaPress\Redirects\Models\TpRedirect;

final readonly class SlugRedirector
{
    public function __construct(
        private RedirectManager $manager,
        private RedirectPolicy $policy,
        private RedirectSuggestionManager $suggestions,
    ) {
    }

    public function createOrIgnore(string $oldPath, string $newPath, string $origin = 'slug_change'): ?TpRedirect
    {
        if ($oldPath === $newPath) {
            return null;
        }

        $existing = TpRedirect::query()
            ->fromSource($oldPath)
            ->first();

        if ($existing instanceof TpRedirect) {
            return $existing;
        }

        if (! $this->policy->shouldAutoApplySlugRedirects()) {
            $this->suggestions->stage($oldPath, $newPath, 301, $origin);

            return null;
        }

        return $this->manager->create([
            'source_path' => $oldPath,
            'target_path' => $newPath,
            'status_code' => 301,
            'is_enabled' => true,
            'origin' => $origin,
            'notes' => 'Auto-generated from slug change.',
        ]);
    }
}
