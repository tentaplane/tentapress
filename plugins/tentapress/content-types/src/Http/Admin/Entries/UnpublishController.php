<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Http\RedirectResponse;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;

final class UnpublishController
{
    public function __invoke(TpContentType $contentType, TpContentEntry $entry): RedirectResponse
    {
        abort_unless((int) $entry->content_type_id === (int) $contentType->id, 404);

        $entry->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return to_route('tp.content-types.entries.edit', ['contentType' => $contentType->id, 'entry' => $entry->id])
            ->with('tp_notice_success', 'Content entry moved back to draft.');
    }
}
