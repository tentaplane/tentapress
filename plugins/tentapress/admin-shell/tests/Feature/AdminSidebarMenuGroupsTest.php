<?php

declare(strict_types=1);

use TentaPress\AdminShell\Admin\Menu\MenuBuilderContract;
use TentaPress\Users\Models\TpUser;

it('builds implicit publishing and structure menu groups for related plugin screens', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Sidebar Admin',
        'email' => 'sidebar-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->artisan('tp:plugins sync')->assertSuccessful();

    foreach ([
        'tentapress/admin-shell',
        'tentapress/blocks',
        'tentapress/builder',
        'tentapress/global-content',
        'tentapress/marketing',
        'tentapress/media',
        'tentapress/menus',
        'tentapress/page-editor',
        'tentapress/pages',
        'tentapress/posts',
        'tentapress/redirects',
        'tentapress/seo',
        'tentapress/taxonomies',
        'tentapress/users',
        'tentapress/workflow',
    ] as $pluginId) {
        $this->artisan('tp:plugins enable '.$pluginId)->assertSuccessful();
    }

    $menu = collect(resolve(MenuBuilderContract::class)->build($user));
    $publishing = $menu->firstWhere('label', 'Publishing');
    $structure = $menu->firstWhere('label', 'Structure');

    expect($publishing)->not->toBeNull();
    expect($publishing['route'])->toBeNull();
    expect($publishing['url'])->toBeNull();
    expect($publishing['position'])->toBe(20);
    expect($structure)->not->toBeNull();
    expect($structure['route'])->toBeNull();
    expect($structure['url'])->toBeNull();
    expect($structure['position'])->toBe(50);

    $publishingChildLabels = collect($publishing['children'] ?? [])->pluck('label')->all();
    $childLabels = collect($structure['children'] ?? [])->pluck('label')->all();

    expect($publishingChildLabels)->toBe(['Pages', 'Posts', 'Media', 'Workflow']);
    expect($childLabels)->toBe(['Menus', 'Marketing', 'SEO', 'Global Content', 'Taxonomies', 'Redirects']);
});

it('renders implicit parent menu groups as submenu toggles instead of links', function (): void {
    $html = view('tentapress-admin::partials.sidebar', [
        'tpMenu' => [
            [
                'label' => 'Structure',
                'route' => null,
                'url' => null,
                'active' => false,
                'children' => [
                    [
                        'label' => 'Menus',
                        'url' => '/admin/menus',
                        'active' => false,
                    ],
                ],
            ],
        ],
    ])->render();

    expect($html)->toContain('>Structure<');
    expect($html)->toContain('aria-controls="tp-admin-submenu-0-structure"');
    expect($html)->not->toContain('href="#"');
    expect($html)->not->toContain('<a href=""');
});
