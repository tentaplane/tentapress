<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Public;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentType;

final class ArchiveController
{
    public function __invoke(string $contentTypeKey): View
    {
        $contentType = TpContentType::query()
            ->where('key', $contentTypeKey)
            ->where('archive_enabled', true)
            ->firstOrFail();

        $entries = $contentType->entries()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('tentapress-content-types::public.archive', [
            'contentType' => $contentType,
            'entries' => $entries,
        ]);
    }
}
