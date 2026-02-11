<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Settings\Services\SettingsStore;

it('returns not found for unknown public page slugs', function (): void {
    TpPage::query()->create([
        'title' => 'Known Page',
        'slug' => 'known-page',
        'status' => 'published',
    ]);

    $this->get('/missing-page')->assertNotFound();
});

it('does not render draft pages on public slug routes', function (): void {
    TpPage::query()->create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'status' => 'draft',
    ]);

    $this->get('/draft-page')->assertNotFound();
});

it('redirects home to the configured home page id when available', function (): void {
    $page = TpPage::query()->create([
        'title' => 'Configured Home',
        'slug' => 'configured-home',
        'status' => 'published',
    ]);

    resolve(SettingsStore::class)->set('site.home_page_id', (string) $page->id);

    $this->get('/')->assertRedirect('/configured-home');
});

it('returns not found from home when no configured or fallback home page exists', function (): void {
    $this->get('/')->assertNotFound();
});
