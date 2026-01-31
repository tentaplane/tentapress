<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'blocks_json' => ['nullable', 'string'],
            'author_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
            'published_at' => ['nullable', 'date'],
        ]);

        $title = (string) $data['title'];

        $rawSlug = trim((string) ($data['slug'] ?? ''));
        $slug = $rawSlug !== '' ? $slugger->unique($rawSlug) : $slugger->unique($title);

        $blocksRaw = json_decode((string) $data['blocks_json'], true);
        $blocks = $this->normalizer->normalize($blocksRaw);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $authorId = (int) ($data['author_id'] ?? 0);
        if ($authorId <= 0) {
            $authorId = $nowUserId ?: 0;
        }

        $post = TpPost::query()->create([
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'layout' => $data['layout'] ?? null,
            'blocks' => $blocks,
            'author_id' => $authorId > 0 ? $authorId : null,
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
            'published_at' => $data['published_at'] ?? null,
        ]);

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post created.');
    }
}
