<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
            'editor_driver' => ['nullable', Rule::in(['blocks', 'page'])],
            'blocks_json' => ['nullable', 'string'],
            'page_doc_json' => ['nullable', 'string'],
        ]);

        $slug = $slugger->unique((string) $data['slug'], ignoreId: (int) $page->id);

        $hasBlocksJson = $request->has('blocks_json');
        $blocksRaw = $hasBlocksJson ? json_decode((string) ($data['blocks_json'] ?? ''), true) : $page->blocks;
        $blocks = $this->normalizer->normalize($blocksRaw);

        $hasPageDocJson = $request->has('page_doc_json');
        $pageDocRaw = $hasPageDocJson ? json_decode((string) ($data['page_doc_json'] ?? ''), true) : $page->content;
        $pageDoc = is_array($pageDocRaw) ? $pageDocRaw : $page->content;
        $editorDriver = $this->resolveEditorDriver($data);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $payload = [
            'title' => (string) $data['title'],
            'slug' => $slug,
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'updated_by' => $nowUserId ?: null,
        ];

        if (Schema::hasColumn('tp_pages', 'content')) {
            $payload['content'] = $pageDoc;
        }
        if (Schema::hasColumn('tp_pages', 'editor_driver')) {
            $payload['editor_driver'] = $editorDriver;
        }

        $page->fill($payload);

        $page->save();

        $returnTo = $request->string('return_to')->toString();

        if ($returnTo === 'editor') {
            return to_route('tp.pages.editor', ['page' => $page->id])
                ->with('tp_notice_success', 'Page updated.');
        }

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page updated.');
    }

    /**
     * @param array<string,mixed> $data
     */
    private function resolveEditorDriver(array $data): string
    {
        $requested = (string) ($data['editor_driver'] ?? 'blocks');
        if ($requested !== 'page') {
            return 'blocks';
        }

        if (! app()->bound('tp.pages.editor.view')) {
            return 'blocks';
        }

        $view = resolve('tp.pages.editor.view');

        return is_string($view) && view()->exists($view) ? 'page' : 'blocks';
    }
}
