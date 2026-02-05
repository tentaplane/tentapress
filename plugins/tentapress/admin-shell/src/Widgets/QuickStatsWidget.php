<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Widgets;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use TentaPress\AdminShell\Admin\Widget\AbstractWidget;

final class QuickStatsWidget extends AbstractWidget
{
    protected string $id = 'tentapress/admin-shell:quick-stats';
    protected string $title = 'Quick Stats';
    protected int $priority = 10;
    protected int $colspan = 3;

    public function canRender(): bool
    {
        // Only render if we have at least one content table
        return Schema::hasTable('tp_pages')
            || Schema::hasTable('tp_posts')
            || Schema::hasTable('tp_media')
            || Schema::hasTable('tp_users');
    }

    public function render(): string
    {
        $stats = $this->collectStats();

        return $this->view('tentapress-admin::widgets.quick-stats', [
            'stats' => $stats,
        ]);
    }

    /**
     * Collect stats defensively - each stat is independent.
     *
     * @return array<int, array{label: string, value: int, route: string|null, icon: string|null}>
     */
    private function collectStats(): array
    {
        return Cache::remember('tp:dashboard:quick-stats', 300, function (): array {
            $stats = [];

            // Pages count (if plugin enabled)
            if (Schema::hasTable('tp_pages')) {
                try {
                    $count = DB::table('tp_pages')->count();
                    $stats[] = [
                        'label' => $count === 1 ? 'Page' : 'Pages',
                        'value' => $count,
                        'route' => Route::has('tp.pages.index') ? 'tp.pages.index' : null,
                        'icon' => 'file',
                    ];
                } catch (\Throwable) {
                    // Silently skip
                }
            }

            // Posts count (if plugin enabled)
            if (Schema::hasTable('tp_posts')) {
                try {
                    $count = DB::table('tp_posts')->count();
                    $stats[] = [
                        'label' => $count === 1 ? 'Post' : 'Posts',
                        'value' => $count,
                        'route' => Route::has('tp.posts.index') ? 'tp.posts.index' : null,
                        'icon' => 'file-text',
                    ];
                } catch (\Throwable) {
                    // Silently skip
                }
            }

            // Media count (if plugin enabled)
            if (Schema::hasTable('tp_media')) {
                try {
                    $count = DB::table('tp_media')->count();
                    $stats[] = [
                        'label' => $count === 1 ? 'Media Item' : 'Media',
                        'value' => $count,
                        'route' => Route::has('tp.media.index') ? 'tp.media.index' : null,
                        'icon' => 'image',
                    ];
                } catch (\Throwable) {
                    // Silently skip
                }
            }

            // Users count (if plugin enabled)
            if (Schema::hasTable('tp_users')) {
                try {
                    $count = DB::table('tp_users')->count();
                    $stats[] = [
                        'label' => $count === 1 ? 'User' : 'Users',
                        'value' => $count,
                        'route' => Route::has('tp.users.index') ? 'tp.users.index' : null,
                        'icon' => 'users',
                    ];
                } catch (\Throwable) {
                    // Silently skip
                }
            }

            return $stats;
        });
    }
}
