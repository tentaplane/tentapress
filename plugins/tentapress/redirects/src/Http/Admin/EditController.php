<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Models\TpRedirectEvent;

final class EditController
{
    public function __invoke(TpRedirect $redirect)
    {
        $events = TpRedirectEvent::query()
            ->where('redirect_id', (int) $redirect->id)
            ->latest('id')
            ->limit(25)
            ->get();

        return view('tentapress-redirects::redirects.form', [
            'redirect' => $redirect,
            'events' => $events,
        ]);
    }
}
