<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Public;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Settings\Services\SettingsStore;

final class HomeController
{
    public function __invoke(SettingsStore $settings)
    {
        $homeId = (int) $settings->get('site.home_page_id', 0);

        $page = null;

        if ($homeId > 0) {
            $page = TpPage::query()->where('id', $homeId)->first();
        }

        if (! $page) {
            // Optional fallback
            $page = TpPage::query()->where('slug', 'home')->first();
        }

        abort_if(! $page, 404);

        $slug = trim((string) $page->slug);
        abort_if($slug === '', 404);

        // Redirect to whatever public page route already handles slug rendering.
        return redirect()->to('/'.ltrim($slug, '/'));
    }
}
