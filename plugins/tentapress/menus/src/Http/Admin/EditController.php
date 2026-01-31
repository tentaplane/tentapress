<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Menus\Services\MenuRenderer;
use TentaPress\Menus\Services\ThemeMenuLocations;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;

final class EditController
{
    public function __invoke(
        TpMenu $menu,
        MenuRenderer $renderer,
        ThemeMenuLocations $locations,
    ): View {
        $items = Schema::hasTable('tp_menu_items') ? $menu->items()->get() : collect();

        $locationList = $locations->all();
        $locationKeys = $locations->keys();

        $locationAssignments = [];
        if ($locationKeys !== [] && Schema::hasTable('tp_menu_locations')) {
            $locationAssignments = TpMenuLocation::query()
                ->whereIn('location_key', $locationKeys)
                ->pluck('menu_id', 'location_key')
                ->map(static fn ($v): ?int => is_numeric($v) && (int) $v > 0 ? (int) $v : null)
                ->all();
        }

        $menus = TpMenu::query()->orderBy('name')->get(['id', 'name']);

        $blogBase = $this->blogBase();

        return view('tentapress-menus::menus.edit', [
            'menu' => $menu,
            'items' => $items,
            'menus' => $menus,
            'locations' => $locationList,
            'locationAssignments' => $locationAssignments,
            'locationsWithMenus' => $renderer->locationsWithMenus(),
            'pages' => $this->pages(),
            'posts' => $this->posts(),
            'blogBase' => $blogBase,
        ]);
    }

    /**
     * @return array<int,TpPage>
     */
    private function pages(): array
    {
        if (! class_exists(TpPage::class) || ! Schema::hasTable('tp_pages')) {
            return [];
        }

        return TpPage::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->all();
    }

    /**
     * @return array<int,TpPost>
     */
    private function posts(): array
    {
        if (! class_exists(TpPost::class) || ! Schema::hasTable('tp_posts')) {
            return [];
        }

        return TpPost::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(200)
            ->get(['id', 'title', 'slug'])
            ->all();
    }

    private function blogBase(): string
    {
        if (! class_exists(SettingsStore::class) || ! app()->bound(SettingsStore::class)) {
            return 'blog';
        }

        $rawBase = trim((string) resolve(SettingsStore::class)->get('site.blog_base', 'blog'), '/');
        if ($rawBase === '') {
            return 'blog';
        }

        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1 ? $rawBase : 'blog';
    }
}
