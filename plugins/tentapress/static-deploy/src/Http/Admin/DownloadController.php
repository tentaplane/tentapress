<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TentaPress\StaticDeploy\Services\StaticExporter;

final class DownloadController
{
    public function __invoke(StaticExporter $exporter): BinaryFileResponse
    {
        $zipPath = $exporter->lastZipPath();

        abort_if($zipPath === null || !is_file($zipPath), 404);

        return response()->download($zipPath, basename((string) $zipPath));
    }
}
