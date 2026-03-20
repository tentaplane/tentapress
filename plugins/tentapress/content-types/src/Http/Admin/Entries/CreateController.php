<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryEditorDriverResolver;
use TentaPress\ContentTypes\Services\ContentTypeFormDataFactory;

final class CreateController
{
    public function __invoke(
        TpContentType $contentType,
        ContentEntryEditorDriverResolver $editorDrivers,
        ContentTypeFormDataFactory $formDataFactory,
    ): View {
        $contentType->load('fields');

        return view('tentapress-content-types::entries.form', [
            'contentType' => $contentType,
            'entry' => new TpContentEntry([
                'status' => 'draft',
                'editor_driver' => 'blocks',
            ]),
            'driverDefinitions' => $editorDrivers->definitions(),
            'relationOptions' => $formDataFactory->relationOptions($contentType),
            'mode' => 'create',
        ]);
    }
}
