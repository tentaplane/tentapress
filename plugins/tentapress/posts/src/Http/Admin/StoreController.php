<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Posts\Services\PostSlugger;
use TentaPress\Posts\Support\BlocksNormalizer;

final readonly class StoreController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
    ) {
    }

    public function __invoke(Request $request, PostSlugger $slugger)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_posts', 'slug')],
            'layout' => ['nullable', 'string', 'max:255'],
            'editor_driver' => ['nullable', Rule::in(['blocks', 'page'])],
            'blocks_json' => ['nullable', 'string'],
            'page_doc_json' => ['nullable', 'string'],
            'author_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
            'published_at' => ['nullable', 'date'],
        ]);

        $title = (string) $data['title'];

        $rawSlug = trim((string) ($data['slug'] ?? ''));
        $slug = $rawSlug !== '' ? $slugger->unique($rawSlug) : $slugger->unique($title);

        $blocksRaw = json_decode((string) ($data['blocks_json'] ?? ''), true);
        $blocks = $this->normalizer->normalize($blocksRaw);

        $pageDocRaw = json_decode((string) ($data['page_doc_json'] ?? ''), true);
        $pageDoc = is_array($pageDocRaw) ? $pageDocRaw : null;
        $editorDriver = $this->resolveEditorDriver($data);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $authorId = (int) ($data['author_id'] ?? 0);
        if ($authorId <= 0) {
            $authorId = $nowUserId ?: 0;
        }

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'author_id' => $authorId > 0 ? $authorId : null,
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
            'published_at' => $data['published_at'] ?? null,
        ];

        if (Schema::hasColumn('tp_posts', 'content')) {
            $payload['content'] = $pageDoc;
        }
        if (Schema::hasColumn('tp_posts', 'editor_driver')) {
            $payload['editor_driver'] = $editorDriver;
        }

        $post = TpPost::query()->create($payload);

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post created.');
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

        if (! app()->bound('tp.posts.editor.view')) {
            return 'blocks';
        }

        $view = resolve('tp.posts.editor.view');

        return is_string($view) && view()->exists($view) ? 'page' : 'blocks';
    }
}
