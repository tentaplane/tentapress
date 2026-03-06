<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use Illuminate\Support\Facades\Auth;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Models\TpRedirectEvent;

final class RedirectAuditLogger
{
    /**
     * @param array<string,mixed> $meta
     */
    public function record(TpRedirect $redirect, string $action, array $meta = []): void
    {
        $actorUserId = Auth::check() && is_object(Auth::user())
            ? (int) (Auth::user()->id ?? 0)
            : null;

        TpRedirectEvent::query()->create([
            'redirect_id' => (int) $redirect->id,
            'action' => $action,
            'source_path' => (string) $redirect->source_path,
            'target_path' => (string) $redirect->target_path,
            'actor_user_id' => $actorUserId ?: null,
            'meta' => $meta,
        ]);
    }
}
