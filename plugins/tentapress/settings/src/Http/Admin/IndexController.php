<?php

declare(strict_types=1);

namespace TentaPress\Settings\Http\Admin;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Settings\Services\SettingsStore;

final class IndexController
{
    public function __invoke(SettingsStore $settings)
    {
        $pages = TpPage::query()->orderBy('title')->get(['id', 'title', 'slug']);

        return view('tentapress-settings::index', [
            'siteTitle' => (string) $settings->get('site.title', ''),
            'tagline' => (string) $settings->get('site.tagline', ''),
            'homePageId' => (string) $settings->get('site.home_page_id', ''),
            'blogBase' => (string) $settings->get('site.blog_base', ''),
            'pages' => $pages,
        ]);
    }
}
