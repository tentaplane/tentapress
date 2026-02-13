<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StartController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'pages_mode' => ['required', 'in:create_only'],
            'settings_mode' => ['required', 'in:merge,overwrite'],
            'include_posts' => ['nullable', 'boolean'],
            'include_media' => ['nullable', 'boolean'],
            'include_seo' => ['nullable', 'boolean'],
        ]);

        $run = Str::lower(Str::random(24));

        $request->session()->put("tp_import.progress.{$run}", [
            'token' => (string) $data['token'],
            'pages_mode' => (string) $data['pages_mode'],
            'settings_mode' => (string) $data['settings_mode'],
            'include_posts' => (bool) ($data['include_posts'] ?? false),
            'include_media' => (bool) ($data['include_media'] ?? false),
            'include_seo' => (bool) ($data['include_seo'] ?? false),
        ]);

        return to_route('tp.import.progress', ['run' => $run]);
    }
}
