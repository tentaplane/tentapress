<?php

declare(strict_types=1);

use TentaPress\Posts\Models\TpPost;

it('publishes scheduled posts with the namespaced command signature', function (): void {
    TpPost::query()->create([
        'title' => 'Scheduled Via Command',
        'slug' => 'scheduled-via-command',
        'status' => 'draft',
        'published_at' => now()->subMinute(),
    ]);

    $this->artisan('tp:posts:publish-scheduled')
        ->expectsOutput('Published 1 scheduled post(s).')
        ->assertSuccessful();

    $post = TpPost::query()->where('slug', 'scheduled-via-command')->firstOrFail();

    expect((string) $post->status)->toBe('published');
});
