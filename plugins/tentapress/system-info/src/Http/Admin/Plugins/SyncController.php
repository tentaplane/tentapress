<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Throwable;
use TentaPress\System\Plugin\PluginRegistry;

final class SyncController
{
    public function __invoke(PluginRegistry $registry): RedirectResponse
    {
        if (! Schema::hasTable('tp_plugins')) {
            return to_route('tp.plugins.index')
                ->with('tp_notice_error', 'Plugin table not found. Run migrations before syncing.');
        }

        try {
            $count = $registry->sync();
            $registry->writeCache();

            return to_route('tp.plugins.index')
                ->with('tp_notice_success', "Synced {$count} plugin(s).");
        } catch (Throwable $e) {
            return to_route('tp.plugins.index')
                ->with('tp_notice_error', $e->getMessage());
        }
    }
}
