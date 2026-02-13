<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Import\Services\Importer;

final class AnalyzeController
{
    public function __invoke(Request $request, Importer $importer)
    {
        $data = $request->validate([
            'bundle' => [
                'required',
                'file',
                'mimes:zip,xml',
                'mimetypes:application/zip,application/x-zip-compressed,text/xml,application/xml,application/rss+xml',
            ],
        ]);

        $result = $importer->analyzeBundle($data['bundle']);

        return view('tentapress-import::review', [
            'token' => $result['token'],
            'summary' => $result['summary'],
            'meta' => $result['meta'],
        ]);
    }
}
