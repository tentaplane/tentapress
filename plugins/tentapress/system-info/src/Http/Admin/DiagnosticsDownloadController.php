<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin;

use Illuminate\Support\Str;
use TentaPress\SystemInfo\Services\SystemInfoService;

final class DiagnosticsDownloadController
{
    public function __invoke(SystemInfoService $service)
    {
        $data = $service->diagnostics();

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        abort_if($json === false, 500, 'Unable to generate diagnostics.');

        $filename = 'tentapress-diagnostics-'.Str::of(now()->format('Ymd-His'))->toString().'.json';

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
