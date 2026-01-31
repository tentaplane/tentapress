<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Auth\Logout;

use Illuminate\Http\Request;

final class StoreController
{
    public function __invoke(Request $request)
    {
        auth()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return to_route('tp.login')
            ->with('tp_notice_success', 'You have been logged out.');
    }
}
