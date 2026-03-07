<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Services\GlobalContentFormData;

final class CreateController
{
    public function __invoke(GlobalContentFormData $formData)
    {
        return view('tentapress-global-content::global-content.form', [
            'mode' => 'create',
            'globalContent' => new TpGlobalContent([
                'kind' => 'section',
                'status' => 'draft',
                'editor_driver' => 'blocks',
                'blocks' => [],
            ]),
            'blocksJson' => '[]',
            'editorDrivers' => $formData->editorDrivers(),
            'blockDefinitions' => $formData->blockDefinitions(),
            'mediaOptions' => $formData->mediaOptions(),
        ]);
    }
}
