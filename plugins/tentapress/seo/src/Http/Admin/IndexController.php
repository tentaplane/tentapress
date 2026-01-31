<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class IndexController
{
    public function __invoke()
    {
        $pages = [];

        if (class_exists(TpPage::class) && Schema::hasTable('tp_pages')) {
            $pages = TpPage::query()
                        ->orderBy('id', 'desc')
                        ->get(['id', 'title', 'slug'])
                        ->all();
        }

        $existing = TpSeoPage::query()->pluck('page_id')->all();
        $existing = array_map(intval(...), is_array($existing) ? $existing : []);

        return view('tentapress-seo::index', [
            'pages' => $pages,
            'hasSeo' => array_fill_keys($existing, true),
        ]);
    }
}
