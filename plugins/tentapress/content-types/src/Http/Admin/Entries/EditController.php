<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryEditorDriverResolver;
use TentaPress\ContentTypes\Services\ContentTypeFormDataFactory;

final class EditController
{
    public function __invoke(
        TpContentType $contentType,
        TpContentEntry $entry,
        ContentEntryEditorDriverResolver $editorDrivers,
        ContentTypeFormDataFactory $formDataFactory,
    ): View {
        abort_unless((int) $entry->content_type_id === (int) $contentType->id, 404);

        $contentType->load('fields');

        return view('tentapress-content-types::entries.form', [
            'contentType' => $contentType,
            'entry' => $entry,
            'driverDefinitions' => $editorDrivers->definitions(),
            'relationOptions' => $formDataFactory->relationOptions($contentType),
            'mode' => 'edit',
        ]);
    }
}
