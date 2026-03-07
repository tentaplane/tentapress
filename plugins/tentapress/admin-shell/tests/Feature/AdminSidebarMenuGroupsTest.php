<?php

declare(strict_types=1);

use TentaPress\AdminShell\Admin\Menu\MenuBuilderContract;
use TentaPress\Users\Models\TpUser;

it('builds an implicit structure menu group for related plugin screens', function (): void {
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
        'tentapress/menus',
        'tentapress/page-editor',
        'tentapress/pages',
        'tentapress/posts',
        'tentapress/taxonomies',
        'tentapress/users',
    ] as $pluginId) {
        $this->artisan('tp:plugins enable '.$pluginId)->assertSuccessful();
    }

    $menu = collect(resolve(MenuBuilderContract::class)->build($user));
    $structure = $menu->firstWhere('label', 'Structure');

    expect($structure)->not->toBeNull();
    expect($structure['route'])->toBeNull();
    expect($structure['url'])->toBeNull();
    expect(collect($structure['children'] ?? [])->pluck('label')->all())->toBe([
        'Taxonomies',
        'Menus',
        'Global Content',
    ]);
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
