<?php

declare(strict_types=1);

namespace TentaPress\Themes\Http\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TentaPress\System\Support\Paths;

final class ScreenshotController
{
    public function __invoke(string $themePath): BinaryFileResponse
    {
        $themeId = trim($themePath, '/');

        $row = DB::table('tp_themes')->where('id', $themeId)->first();

        abort_unless($row, 404);

        $relativePath = (string) ($row->path ?? '');

        abort_if($relativePath === '', 404);

        $file = $this->resolveScreenshotFile($relativePath);

        abort_if($file === null, 404);

        $mime = File::mimeType($file) ?: 'application/octet-stream';

        return response()->file($file, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function resolveScreenshotFile(string $themeRelativePath): ?string
    {
        $candidates = [
            'screenshot.png',
            'screenshot.jpg',
            'screenshot.jpeg',
            'screenshot.webp',
        ];

        foreach ($candidates as $name) {
            $full = Paths::themesPath($themeRelativePath.'/'.$name);

            if (File::exists($full) && File::isFile($full)) {
                return $full;
            }
        }

        return null;
    }
}
