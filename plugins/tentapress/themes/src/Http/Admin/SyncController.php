<?php

declare(strict_types=1);

namespace TentaPress\Themes\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Throwable;
use TentaPress\System\Support\RuntimeCacheRefresher;
use TentaPress\System\Theme\ThemeManager;
use TentaPress\System\Theme\ThemeRegistry;

final class SyncController
{
    public function __invoke(
        ThemeRegistry $registry,
        ThemeManager $manager,
        RuntimeCacheRefresher $runtimeCacheRefresher
    ): RedirectResponse {
        if (! Schema::hasTable('tp_themes')) {
            return to_route('tp.themes.index')
                ->with('tp_notice_error', 'Theme table not found. Run migrations before syncing.');
        }

        try {
            $count = $registry->sync();
            $manager->writeCache();
            $runtimeCacheRefresher->refreshAfterThemeChange();

            return to_route('tp.themes.index')
                ->with('tp_notice_success', "Synced {$count} theme(s).");
        } catch (Throwable $e) {
            return to_route('tp.themes.index')
                ->with('tp_notice_error', $e->getMessage());
        }
    }
}
