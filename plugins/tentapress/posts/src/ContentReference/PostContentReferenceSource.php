<?php

declare(strict_types=1);

namespace TentaPress\Posts\ContentReference;

use TentaPress\Posts\Models\TpPost;
use TentaPress\System\ContentReference\ContentReference;
use TentaPress\System\ContentReference\ContentReferenceSource;

final class PostContentReferenceSource implements ContentReferenceSource
{
    public function key(): string
    {
        return 'posts';
    }

    public function find(string $id): ?ContentReference
    {
        $post = TpPost::query()->find((int) $id);

        if (! $post instanceof TpPost) {
            return null;
        }

        return new ContentReference(
            source: $this->key(),
            id: (string) $post->getKey(),
            title: $this->titleFor($post),
            typeLabel: 'Post',
        );
    }

    public function options(array $constraints = []): array
    {
        return TpPost::query()
            ->orderBy('title')
            ->get()
            ->map(fn (TpPost $post): ContentReference => new ContentReference(
                source: $this->key(),
                id: (string) $post->getKey(),
                title: $this->titleFor($post),
                typeLabel: 'Post',
            ))
            ->all();
    }

    private function titleFor(TpPost $post): string
    {
        $title = trim((string) $post->title);

        return $title !== '' ? $title : 'Post #'.$post->getKey();
    }
}
