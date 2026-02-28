<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TentaPress\StaticDeploy\Services\StaticExporter;

final class DownloadController
{
    public function __invoke(StaticExporter $exporter, ?string $timestamp = null): BinaryFileResponse
    {
        $zipPath = $timestamp === null
            ? $exporter->lastZipPath()
            : $exporter->zipPathForTimestamp($timestamp);

        abort_if($zipPath === null || !is_file($zipPath), 404);

        return response()->download($zipPath, basename((string) $zipPath));
    }
}
