<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use TentaPress\Redirects\Http\Requests\StoreRedirectRequest;
use TentaPress\Redirects\Services\RedirectManager;

final class StoreController
{
    public function __invoke(StoreRedirectRequest $request, RedirectManager $manager): RedirectResponse
    {
        $data = $request->validated();

        try {
            $redirect = $manager->create([
                'source_path' => (string) $data['source_path'],
                'target_path' => (string) $data['target_path'],
                'status_code' => (int) $data['status_code'],
                'is_enabled' => (bool) ($data['is_enabled'] ?? false),
                'origin' => 'manual',
                'notes' => isset($data['notes']) ? (string) $data['notes'] : null,
                'created_by' => Auth::check() ? (int) (Auth::user()->id ?? 0) : null,
                'updated_by' => Auth::check() ? (int) (Auth::user()->id ?? 0) : null,
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors([
                'source_path' => $exception->getMessage(),
            ]);
        }

        return to_route('tp.redirects.edit', ['redirect' => $redirect->id])
            ->with('tp_notice_success', 'Redirect created.');
    }
}
