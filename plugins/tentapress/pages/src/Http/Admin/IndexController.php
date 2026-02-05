<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('s', ''));
        $sort = (string) $request->query('sort', 'updated');
        $requestedDirection = strtolower((string) $request->query('direction', 'asc'));

        $sortColumns = [
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'status',
            'updated' => 'updated_at',
        ];
        $sort = array_key_exists($sort, $sortColumns) ? $sort : 'updated';

        $defaultDirection = in_array($sort, ['title', 'slug', 'status'], true) ? 'asc' : 'desc';
        $direction = in_array($requestedDirection, ['asc', 'desc'], true) ? $requestedDirection : $defaultDirection;

        $query = TpPage::query()
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('id');

        if ($status === 'draft' || $status === 'published') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($qq) use ($search): void {
                $qq->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('slug', '%'.$search.'%');
            });
        }

        $pages = $query->paginate(20)->withQueryString();
        $menuUsage = $this->menuUsageForPageCollection($pages->getCollection());

        return view('tentapress-pages::pages.index', [
            'pages' => $pages,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'menuUsage' => $menuUsage,
        ]);
    }

    /**
     * @param Collection<int, TpPage> $pages
     * @return array<int, array{count:int, menus:array<int, string>}>
     */
    private function menuUsageForPageCollection(Collection $pages): array
    {
        if ($pages->isEmpty()) {
            return [];
        }

        if (! Schema::hasTable('tp_menu_items') || ! Schema::hasTable('tp_menus')) {
            return [];
        }

        $pageIdsByPath = [];

        foreach ($pages as $page) {
            $slug = trim((string) $page->slug, '/');
            $path = $slug === '' ? '/' : '/'.$slug;

            $pageIdsByPath[$path] ??= [];
            $pageIdsByPath[$path][] = (int) $page->id;

            if ($path !== '/') {
                $pageIdsByPath[$path.'/'] ??= [];
                $pageIdsByPath[$path.'/'][] = (int) $page->id;
            }
        }

        if ($pageIdsByPath === []) {
            return [];
        }

        $menuRows = DB::table('tp_menu_items as items')
            ->join('tp_menus as menus', 'menus.id', '=', 'items.menu_id')
            ->whereIn('items.url', array_keys($pageIdsByPath))
            ->select('items.url', 'menus.name')
            ->get();

        $usage = [];

        $menuRows->each(function (object $row) use (&$usage, $pageIdsByPath): void {
            $path = (string) ($row->url ?? '');
            $menuName = trim((string) ($row->name ?? ''));

            if ($menuName === '' || ! array_key_exists($path, $pageIdsByPath)) {
                return;
            }

            foreach ($pageIdsByPath[$path] as $pageId) {
                $usage[$pageId] ??= [];
                $usage[$pageId][$menuName] = true;
            }
        });

        $normalized = [];

        foreach ($usage as $pageId => $menus) {
            $menuNames = array_keys($menus);
            sort($menuNames);

            $normalized[(int) $pageId] = [
                'count' => count($menuNames),
                'menus' => $menuNames,
            ];
        }

        return $normalized;
    }
}
