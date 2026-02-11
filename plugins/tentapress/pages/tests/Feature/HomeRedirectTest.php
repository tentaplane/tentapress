<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;

it('redirects home route to the home page slug when present', function (): void {
    TpPage::query()->create([
        'title' => 'Home',
        'slug' => 'home',
        'status' => 'published',
    ]);

    $response = $this->get('/');

    $response->assertRedirect('/home');
});
