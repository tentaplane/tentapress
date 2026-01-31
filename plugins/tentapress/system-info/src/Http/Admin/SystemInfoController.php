<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin;

use TentaPress\SystemInfo\Services\SystemInfoService;

final class SystemInfoController
{
    public function __invoke(SystemInfoService $service)
    {
        return view('tentapress-system-info::index', [
            'report' => $service->report(),
        ]);
    }
}
