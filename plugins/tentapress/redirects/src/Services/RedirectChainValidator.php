<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use TentaPress\Redirects\Models\TpRedirect;

final class RedirectChainValidator
{
    public function hasCycle(string $sourcePath, string $targetPath, ?int $ignoreRedirectId = null): bool
    {
        if ($sourcePath === $targetPath) {
            return true;
        }

        $visited = [$sourcePath => true];
        $cursor = $targetPath;

        for ($i = 0; $i < 20; $i++) {
            if (isset($visited[$cursor])) {
                return true;
            }

            $visited[$cursor] = true;

            $query = TpRedirect::query()
                ->enabled()
                ->fromSource($cursor);

            if ($ignoreRedirectId !== null) {
                $query->where('id', '!=', $ignoreRedirectId);
            }

            $next = $query->value('target_path');

            if (! is_string($next) || $next === '') {
                return false;
            }

            $cursor = $next;
        }

        return true;
    }
}
