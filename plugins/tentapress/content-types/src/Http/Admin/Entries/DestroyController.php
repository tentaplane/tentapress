<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Http\RedirectResponse;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;

final class DestroyController
{
    public function __invoke(TpContentType $contentType, TpContentEntry $entry): RedirectResponse
    {
        abort_unless((int) $entry->content_type_id === (int) $contentType->id, 404);

        $entry->delete();

        return to_route('tp.content-types.entries.index', ['contentType' => $contentType->id])
            ->with('tp_notice_success', 'Content entry deleted.');
    }
}
