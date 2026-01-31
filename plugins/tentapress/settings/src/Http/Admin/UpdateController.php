<?php

declare(strict_types=1);

namespace TentaPress\Settings\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Settings\Services\SettingsStore;

final class UpdateController
{
    public function __invoke(Request $request, SettingsStore $settings)
    {
        $data = $request->validate([
            'site_title' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'home_page_id' => ['nullable', 'integer'],
            'blog_base' => ['nullable', 'string', 'max:60', 'regex:/^(?:[a-z0-9]+(?:-[a-z0-9]+)*)?$/'],
        ]);

        $settings->set('site.title', (string) ($data['site_title'] ?? ''), true);
        $settings->set('site.tagline', (string) ($data['tagline'] ?? ''), true);

        $home = isset($data['home_page_id']) ? (int) $data['home_page_id'] : 0;
        $settings->set('site.home_page_id', $home > 0 ? (string) $home : '', true);

        $blogBase = isset($data['blog_base']) ? trim((string) $data['blog_base']) : '';
        $blogBase = trim($blogBase, '/');
        $settings->set('site.blog_base', $blogBase, true);

        return to_route('tp.settings.index')
            ->with('tp_notice_success', 'Settings saved.');
    }
}
