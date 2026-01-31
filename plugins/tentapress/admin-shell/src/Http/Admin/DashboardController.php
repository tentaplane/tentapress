<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Http\Admin;

final class DashboardController
{
    public function __invoke()
    {
        return view('tentapress-admin::dashboard');
    }
}
