<?php

declare(strict_types=1);

namespace TentaPress\Posts\Jobs;

use TentaPress\Posts\Models\TpPost;

final readonly class PublishScheduledPosts
{
    public function __construct(
        private int $limit = 50,
    ) {
    }

    public function handle(): int
    {
        $now = now();

        $posts = TpPost::query()
            ->where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->oldest('published_at')
            ->limit($this->limit)
            ->get();

        $published = 0;

        foreach ($posts as $post) {
            $post->status = 'published';
            $post->save();
            $published++;
        }

        return $published;
    }
}
