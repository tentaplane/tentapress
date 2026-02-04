<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Pages\Services\PageSlugger;
use TentaPress\Pages\Support\BlocksNormalizer;

final readonly class StoreController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
    ) {
    }

    public function __invoke(Request $request, PageSlugger $slugger)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_pages', 'slug')],
            'layout' => ['nullable', 'string', 'max:255'],
            'blocks_json' => ['nullable', 'string'],
            'page_doc_json' => ['nullable', 'string'],
        ]);

        $title = (string) $data['title'];

        $rawSlug = trim((string) ($data['slug'] ?? ''));
        $slug = $rawSlug !== '' ? $slugger->unique($rawSlug) : $slugger->unique($title);

        $blocksRaw = json_decode((string) ($data['blocks_json'] ?? ''), true);
        $blocks = $this->normalizer->normalize($blocksRaw);

        $pageDocRaw = json_decode((string) ($data['page_doc_json'] ?? ''), true);
        $pageDoc = is_array($pageDocRaw) ? $pageDocRaw : null;

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $page = TpPage::query()->create([
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'content' => $pageDoc,
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
            'published_at' => null,
        ]);

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page created.');
    }
}
