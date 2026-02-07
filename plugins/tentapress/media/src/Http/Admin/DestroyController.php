<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Models\TpMedia;

final class DestroyController
{
    public function __invoke(TpMedia $media): RedirectResponse
    {
        $disk = $media->disk !== null && $media->disk !== '' ? $media->disk : 'public';
        $path = (string) ($media->path ?? '');

        if ($path !== '') {
            $this->deleteFromDisk($disk, $path);
        }

        $media->delete();

        return to_route('tp.media.index')
            ->with('tp_notice_success', 'Media deleted.');
    }

    private function deleteFromDisk(string $disk, string $path): void
    {
        $storage = Storage::disk($disk);

        foreach ($this->candidatePaths($path) as $candidatePath) {
            if ($candidatePath === '') {
                continue;
            }

            if ($storage->exists($candidatePath)) {
                $storage->delete($candidatePath);

                return;
            }
        }
    }

    /**
     * @return array<int,string>
     */
    private function candidatePaths(string $path): array
    {
        $candidates = [
            trim($path),
        ];

        $urlPath = parse_url($path, PHP_URL_PATH);
        if (is_string($urlPath) && $urlPath !== '') {
            $candidates[] = trim($urlPath);
        }

        $normalized = [];
        foreach ($candidates as $candidate) {
            $trimmed = trim($candidate);
            if ($trimmed === '') {
                continue;
            }

            $normalized[] = $trimmed;
            $normalized[] = ltrim($trimmed, '/');

            $withoutLeadingSlash = ltrim($trimmed, '/');
            if (str_starts_with($withoutLeadingSlash, 'storage/')) {
                $normalized[] = substr($withoutLeadingSlash, strlen('storage/'));
            }

            if (str_starts_with($withoutLeadingSlash, 'public/')) {
                $normalized[] = substr($withoutLeadingSlash, strlen('public/'));
            }
        }

        return array_values(array_unique(array_filter($normalized, static fn (string $value): bool => $value !== '')));
    }
}
