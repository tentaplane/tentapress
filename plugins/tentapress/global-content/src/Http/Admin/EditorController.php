<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Services\GlobalContentFormData;

final class EditorController
{
    public function __invoke(TpGlobalContent $globalContent, GlobalContentFormData $formData)
    {
        $blocksJson = json_encode(is_array($globalContent->blocks) ? array_values($globalContent->blocks) : [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return view('tentapress-global-content::global-content.form', [
            'mode' => 'edit',
            'editorMode' => true,
            'globalContent' => $globalContent,
            'blocksJson' => is_string($blocksJson) ? $blocksJson : '[]',
            'editorDrivers' => $formData->editorDrivers(),
            'blockDefinitions' => $formData->blockDefinitions(),
            'mediaOptions' => $formData->mediaOptions(),
        ]);
    }
}
