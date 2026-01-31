<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Auth\Login;

final class CreateController
{
    public function __invoke()
    {
        if (auth()->check()) {
            return redirect()->to('/admin');
        }

        return view('tentapress-users::auth.login', [
            'status' => session('status'),
        ]);
    }
}
