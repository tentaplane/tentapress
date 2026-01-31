<?php

declare(strict_types=1);

namespace TentaPress\Menus\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;

final readonly class MenuEditorSaver
{
    public function __construct(
        private MenuSlugger $slugger,
        private ThemeMenuLocations $locations,
    ) {
    }

    public function updateMenu(TpMenu $menu, array $data, ?int $userId): void
    {
        if (! $this->tablesExist()) {
            return;
        }

        $name = trim((string) ($data['name'] ?? $menu->name ?? ''));
        $slugInput = trim((string) ($data['slug'] ?? ''));
        $slug = $slugInput !== '' ? $this->slugger->unique($slugInput, (int) $menu->id) : $this->slugger->unique($name, (int) $menu->id);

        $menu->fill([
            'name' => $name !== '' ? $name : 'Menu',
            'slug' => $slug,
            'updated_by' => $userId,
        ]);
        $menu->save();

        $this->syncItems($menu, Arr::get($data, 'items', []));
        $this->syncLocations(Arr::get($data, 'locations', []));
    }

    /**
     * @param mixed $itemsRaw
     */
    private function syncItems(TpMenu $menu, mixed $itemsRaw): void
    {
        $itemsRaw = is_array($itemsRaw) ? $itemsRaw : [];

        $existing = TpMenuItem::query()
            ->where('menu_id', (int) $menu->id)
            ->get()
            ->keyBy('id');

        $keptIds = [];

        foreach ($itemsRaw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = isset($row['id']) && is_numeric($row['id']) ? (int) $row['id'] : 0;
            $title = trim((string) ($row['title'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));

            if ($title === '' && $url === '') {
                continue;
            }

            $parentId = isset($row['parent_id']) && is_numeric($row['parent_id']) ? (int) $row['parent_id'] : null;
            $parentId = $parentId !== null && $existing->has($parentId) ? $parentId : null;

            $sortOrder = isset($row['sort_order']) && is_numeric($row['sort_order']) ? (int) $row['sort_order'] : 0;
            $target = trim((string) ($row['target'] ?? ''));
            $target = in_array($target, ['_blank', '_self'], true) ? $target : null;

            if ($id > 0 && $existing->has($id)) {
                /** @var TpMenuItem $item */
                $item = $existing->get($id);
                $item->fill([
                    'title' => $title !== '' ? $title : $item->title,
                    'url' => $url !== '' ? $url : '#',
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                    'target' => $target,
                ]);
                $item->save();
                $keptIds[] = (int) $item->id;

                continue;
            }

            $item = TpMenuItem::query()->create([
                'menu_id' => (int) $menu->id,
                'title' => $title !== '' ? $title : $url,
                'url' => $url !== '' ? $url : '#',
                'parent_id' => $parentId,
                'sort_order' => $sortOrder,
                'target' => $target,
            ]);

            $keptIds[] = (int) $item->id;
        }

        if ($keptIds === []) {
            TpMenuItem::query()->where('menu_id', (int) $menu->id)->delete();

            return;
        }

        TpMenuItem::query()
            ->where('menu_id', (int) $menu->id)
            ->whereNotIn('id', $keptIds)
            ->delete();

        TpMenuItem::query()
            ->where('menu_id', (int) $menu->id)
            ->whereNotIn('parent_id', $keptIds)
            ->update(['parent_id' => null]);
    }

    /**
     * @param mixed $locationsRaw
     */
    private function syncLocations(mixed $locationsRaw): void
    {
        $locationsRaw = is_array($locationsRaw) ? $locationsRaw : [];
        $locationKeys = $this->locations->keys();

        if ($locationKeys === []) {
            return;
        }

        foreach ($locationKeys as $key) {
            $menuId = $locationsRaw[$key] ?? null;
            $menuId = is_numeric($menuId) && (int) $menuId > 0 ? (int) $menuId : null;

            if ($menuId === null) {
                TpMenuLocation::query()->where('location_key', $key)->delete();

                continue;
            }

            TpMenuLocation::query()->updateOrCreate(
                ['location_key' => $key],
                ['menu_id' => $menuId],
            );
        }
    }

    private function tablesExist(): bool
    {
        return Schema::hasTable('tp_menus')
            && Schema::hasTable('tp_menu_items')
            && Schema::hasTable('tp_menu_locations');
    }
}
