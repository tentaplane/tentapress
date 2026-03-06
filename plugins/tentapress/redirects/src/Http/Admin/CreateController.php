<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

final class CreateController
{
    public function __invoke()
    {
        return view('tentapress-redirects::redirects.form', [
            'redirect' => null,
        ]);
    }
}
