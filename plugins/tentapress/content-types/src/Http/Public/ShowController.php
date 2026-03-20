<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Public;

use Illuminate\Http\Response;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryRenderer;

final class ShowController
{
    public function __invoke(string $contentTypeKey, string $slug, ContentEntryRenderer $renderer): Response
    {
        $contentType = TpContentType::query()->where('key', $contentTypeKey)->firstOrFail();

        $entry = TpContentEntry::query()
            ->with('contentType.fields')
            ->where('content_type_id', $contentType->id)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $renderer->render($entry);
    }
}
