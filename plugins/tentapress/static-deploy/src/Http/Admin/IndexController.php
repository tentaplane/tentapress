<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin;

use TentaPress\StaticDeploy\Services\StaticExporter;

final class IndexController
{
    public function __invoke(
        StaticExporter $exporter
    ) {
        return view('tentapress-static-deploy::index', [
            'last' => $exporter->lastBuildInfo(),
        ]);
    }
}
