<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Import\Services\Importer;

final class RunController
{
    public function __invoke(Request $request, Importer $importer)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'pages_mode' => ['required', 'in:create_only'],
            'settings_mode' => ['required', 'in:merge,overwrite'],
            'include_posts' => ['nullable', 'boolean'],
            'include_media' => ['nullable', 'boolean'],
            'include_seo' => ['nullable', 'boolean'],
        ]);

        $result = $importer->runImport($data['token'], [
            'pages_mode' => (string) $data['pages_mode'],
            'settings_mode' => (string) $data['settings_mode'],
            'include_posts' => (bool) ($data['include_posts'] ?? false),
            'include_media' => (bool) ($data['include_media'] ?? false),
            'include_seo' => (bool) ($data['include_seo'] ?? false),
            'actor_user_id' => (int) ($request->user()?->id ?? 0),
        ]);

        return to_route('tp.import.index')
            ->with('tp_notice_success', $result['message']);
    }
}
