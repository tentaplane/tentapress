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

final readonly class UpdateController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
    ) {
    }

    public function __invoke(Request $request, TpPost $post, PostSlugger $slugger)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_posts', 'slug')->ignore($post->id)],
            'layout' => ['nullable', 'string', 'max:255'],
            'editor_driver' => ['nullable', Rule::in(['blocks', 'page'])],
            'blocks_json' => ['nullable', 'string'],
            'page_doc_json' => ['nullable', 'string'],
            'author_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = $slugger->unique((string) $data['slug'], ignoreId: (int) $post->id);

        $hasBlocksJson = $request->has('blocks_json');
        $blocksRaw = $hasBlocksJson ? json_decode((string) ($data['blocks_json'] ?? ''), true) : $post->blocks;
        $blocks = $this->normalizer->normalize($blocksRaw);

        $hasPageDocJson = $request->has('page_doc_json');
        $pageDocRaw = $hasPageDocJson ? json_decode((string) ($data['page_doc_json'] ?? ''), true) : $post->content;
        $pageDoc = is_array($pageDocRaw) ? $pageDocRaw : $post->content;
        $editorDriver = $this->resolveEditorDriver($data);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $authorId = (int) ($data['author_id'] ?? 0);
        $authorId = $authorId > 0 ? $authorId : null;

        $payload = [
            'title' => (string) $data['title'],
            'slug' => $slug,
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'author_id' => $authorId,
            'published_at' => $data['published_at'] ?? $post->published_at,
            'updated_by' => $nowUserId ?: null,
        ];

        if (Schema::hasColumn('tp_posts', 'content')) {
            $payload['content'] = $pageDoc;
        }
        if (Schema::hasColumn('tp_posts', 'editor_driver')) {
            $payload['editor_driver'] = $editorDriver;
        }

        $post->fill($payload);

        $post->save();

        $returnTo = $request->string('return_to')->toString();

        if ($returnTo === 'editor') {
            return to_route('tp.posts.editor', ['post' => $post->id])
                ->with('tp_notice_success', 'Post updated.');
        }

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post updated.');
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
