<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Models\TpSeoPost;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $type = (string) $request->query('type', 'all');
        $type = in_array($type, ['all', 'pages', 'posts'], true) ? $type : 'all';
        $seo = (string) $request->query('seo', 'all');
        $seo = in_array($seo, ['all', 'custom', 'missing'], true) ? $seo : 'all';
        $search = trim((string) $request->query('s', ''));
        $sort = (string) $request->query('sort', 'title');
        $sort = in_array($sort, ['title', 'slug', 'updated', 'type', 'custom'], true) ? $sort : 'title';
        $direction = $request->query('direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $pageSeoIds = [];
        if (Schema::hasTable('tp_seo_pages')) {
            $pageSeoIds = TpSeoPage::query()->pluck('page_id')->all();
            $pageSeoIds = array_map(intval(...), is_array($pageSeoIds) ? $pageSeoIds : []);
        }

        $postSeoIds = [];
        if (Schema::hasTable('tp_seo_posts')) {
            $postSeoIds = TpSeoPost::query()->pluck('post_id')->all();
            $postSeoIds = array_map(intval(...), is_array($postSeoIds) ? $postSeoIds : []);
        }

        $entries = [];

        if ($type !== 'posts' && class_exists(TpPage::class) && Schema::hasTable('tp_pages')) {
            $pagesQuery = TpPage::query()->select(['id', 'title', 'slug', 'updated_at']);

            if ($search !== '') {
                $pagesQuery->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            }

            foreach ($pagesQuery->get() as $page) {
                $id = (int) $page->id;
                $entries[] = [
                    'type' => 'page',
                    'id' => $id,
                    'title' => (string) ($page->title ?? ''),
                    'slug' => (string) ($page->slug ?? ''),
                    'updated_at' => $page->updated_at,
                    'has_seo' => in_array($id, $pageSeoIds, true),
                    'edit_url' => route('tp.seo.pages.edit', ['page' => $id]),
                ];
            }
        }

        if ($type !== 'pages' && class_exists(TpPost::class) && Schema::hasTable('tp_posts')) {
            $postsQuery = TpPost::query()->select(['id', 'title', 'slug', 'updated_at']);

            if ($search !== '') {
                $postsQuery->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            }

            foreach ($postsQuery->get() as $post) {
                $id = (int) $post->id;
                $entries[] = [
                    'type' => 'post',
                    'id' => $id,
                    'title' => (string) ($post->title ?? ''),
                    'slug' => (string) ($post->slug ?? ''),
                    'updated_at' => $post->updated_at,
                    'has_seo' => in_array($id, $postSeoIds, true),
                    'edit_url' => route('tp.posts.edit', ['post' => $id]),
                ];
            }
        }

        if ($seo !== 'all') {
            $entries = array_values(array_filter($entries, fn (array $entry): bool => $seo === 'custom' ? (bool) $entry['has_seo'] : ! $entry['has_seo']));
        }

        usort($entries, function (array $a, array $b) use ($sort, $direction): int {
            $dir = $direction === 'asc' ? 1 : -1;

            return match ($sort) {
                'title' => $dir * strnatcasecmp($a['title'], $b['title']),
                'slug' => $dir * strnatcasecmp($a['slug'], $b['slug']),
                'type' => $dir * strnatcasecmp($a['type'], $b['type']),
                'custom' => $dir * ((int) $a['has_seo'] <=> (int) $b['has_seo']),
                default => $dir * (((int) ($a['updated_at']?->getTimestamp() ?? 0)) <=> ((int) ($b['updated_at']?->getTimestamp() ?? 0))),
            };
        });

        $perPage = 25;
        $page = max((int) $request->query('page', 1), 1);
        $total = count($entries);
        $offset = ($page - 1) * $perPage;
        $pagedEntries = array_slice($entries, $offset, $perPage);
        $paginator = new LengthAwarePaginator(
            $pagedEntries,
            $total,
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        return view('tentapress-seo::index', [
            'entries' => $paginator,
            'total' => $total,
            'type' => $type,
            'seo' => $seo,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
