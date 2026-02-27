<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;

it('does not persist rewritten blocks in dry-run mode', function (): void {
    $page = TpPage::query()->create([
        'title' => 'Newsletter Page',
        'slug' => 'newsletter-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/newsletter',
                'props' => [
                    'title' => 'Join',
                    'body' => 'Monthly updates',
                    'actions' => [
                        ['label' => 'Subscribe', 'url' => 'https://example.test/subscribe'],
                    ],
                ],
            ],
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Keep me'],
            ],
        ],
    ]);

    $this->artisan('tp:forms:migrate-newsletter', ['--dry-run' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Total changed:');

    $page->refresh();

    expect($page->blocks[0]['type'] ?? null)->toBe('blocks/newsletter');
    expect($page->blocks[1]['type'] ?? null)->toBe('blocks/content');
});

it('rewrites newsletter blocks to forms/signup in write mode', function (): void {
    $post = TpPost::query()->create([
        'title' => 'Newsletter Post',
        'slug' => 'newsletter-post',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/newsletter',
                'props' => [
                    'title' => 'Join',
                    'body' => 'Weekly updates',
                    'actions' => [
                        ['label' => 'Subscribe', 'url' => 'https://example.test/subscribe'],
                    ],
                ],
            ],
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Keep me'],
            ],
        ],
    ]);

    $this->artisan('tp:forms:migrate-newsletter')
        ->assertSuccessful()
        ->expectsOutputToContain('Total changed:');

    $post->refresh();

    expect($post->blocks[0]['type'] ?? null)->toBe('forms/signup');
    expect($post->blocks[1]['type'] ?? null)->toBe('blocks/content');
    expect($post->blocks[0]['props']['provider'] ?? null)->toBe('mailchimp');
    expect($post->blocks[0]['props']['mailchimp_action_url'] ?? null)->toBe('https://example.test/subscribe');
});
