<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use TentaPress\Redirects\Http\Requests\UpdateRedirectRequest;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Services\RedirectManager;

final class UpdateController
{
    public function __invoke(UpdateRedirectRequest $request, TpRedirect $redirect, RedirectManager $manager): RedirectResponse
    {
        $data = $request->validated();

        try {
            $manager->update($redirect, [
                'source_path' => (string) $data['source_path'],
                'target_path' => (string) $data['target_path'],
                'status_code' => (int) $data['status_code'],
                'is_enabled' => (bool) ($data['is_enabled'] ?? false),
                'origin' => (string) ($redirect->origin ?? 'manual'),
                'notes' => isset($data['notes']) ? (string) $data['notes'] : null,
                'updated_by' => Auth::check() ? (int) (Auth::user()->id ?? 0) : null,
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors([
                'source_path' => $exception->getMessage(),
            ]);
        }

        return to_route('tp.redirects.edit', ['redirect' => $redirect->id])
            ->with('tp_notice_success', 'Redirect updated.');
    }
}
