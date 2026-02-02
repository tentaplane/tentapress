<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Pages\Services\PageSlugger;
use TentaPress\Pages\Support\BlocksNormalizer;

final readonly class UpdateController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
    ) {
    }

    public function __invoke(Request $request, TpPage $page, PageSlugger $slugger)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_pages', 'slug')->ignore($page->id)],
            'layout' => ['nullable', 'string', 'max:255'],
            'blocks_json' => ['nullable', 'string'],
        ]);

        $slug = $slugger->unique((string) $data['slug'], ignoreId: (int) $page->id);

        $blocksRaw = json_decode((string) $data['blocks_json'], true);
        $blocks = $this->normalizer->normalize($blocksRaw);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $page->fill([
            'title' => (string) $data['title'],
            'slug' => $slug,
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'updated_by' => $nowUserId ?: null,
        ]);

        $page->save();

        $returnTo = $request->string('return_to')->toString();

        if ($returnTo === 'editor') {
            return to_route('tp.pages.editor', ['page' => $page->id])
                ->with('tp_notice_success', 'Page updated.');
        }

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page updated.');
    }
}
