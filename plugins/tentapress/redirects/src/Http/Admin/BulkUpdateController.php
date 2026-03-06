<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Services\RedirectAuditLogger;

final class BulkUpdateController
{
    public function __invoke(Request $request, RedirectAuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
            'action' => ['required', 'in:enable,disable'],
        ]);

        $enable = (string) $data['action'] === 'enable';
        $ids = array_values(array_unique(array_map(static fn ($value): int => (int) $value, (array) $data['ids'])));

        $updated = TpRedirect::query()
            ->whereIn('id', $ids)
            ->update([
                'is_enabled' => $enable,
                'updated_at' => now(),
            ]);

        $redirects = TpRedirect::query()->whereIn('id', $ids)->get();
        foreach ($redirects as $redirect) {
            $auditLogger->record($redirect, $enable ? 'bulk_enabled' : 'bulk_disabled');
        }

        return to_route('tp.redirects.index')
            ->with('tp_notice_success', "Bulk update complete. {$updated} redirect(s) updated.");
    }
}
