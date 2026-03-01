<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use TentaPress\Users\Models\TpUser;

it('redirects guests from plugin catalog to login', function (): void {
    $this->get('/admin/plugins/catalog')->assertRedirect('/admin/login');
});

it('allows users with view_system_info capability to view plugin catalog', function (): void {
    $user = makeUserWithCapability('view_system_info', 'viewer-catalog@example.test');

    config()->set('tentapress-system-info.catalog.url', 'https://catalog.example.test/plugins.json');

    Http::fake([
        'https://catalog.example.test/plugins.json' => Http::response([
            'schema_version' => 1,
            'plugins' => [
                [
                    'id' => 'tentapress/forms',
                    'name' => 'Forms',
                    'description' => 'Forms plugin from hosted feed.',
                    'latest_version' => '0.4.3',
                    'package' => 'tentapress/forms',
                ],
            ],
        ], 200),
    ]);

    $this->actingAs($user)
        ->get('/admin/plugins/catalog')
        ->assertOk()
        ->assertSee('Plugin Catalogue')
        ->assertSee('Forms')
        ->assertSee('tentapress/forms');
});

it('fetches the default github blob catalog url through the raw content endpoint', function (): void {
    $user = makeUserWithCapability('view_system_info', 'viewer-catalog-blob@example.test');

    config()->set('tentapress-system-info.catalog.url', 'https://github.com/tentaplane/tentapress/blob/main/docs/catalog/first-party-plugins.json');

    Http::fake([
        'https://raw.githubusercontent.com/tentaplane/tentapress/main/docs/catalog/first-party-plugins.json' => Http::response([
            'schema_version' => 1,
            'plugins' => [
                [
                    'id' => 'tentapress/forms',
                    'name' => 'Forms',
                    'description' => 'Forms plugin from raw github feed.',
                    'latest_version' => '0.4.3',
                    'package' => 'tentapress/forms',
                ],
            ],
        ], 200),
    ]);

    $this->actingAs($user)
        ->get('/admin/plugins/catalog')
        ->assertOk()
        ->assertSee('Forms')
        ->assertSee('tentapress/forms');
});

it('shows local-only plugins when hosted feed is unavailable', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Catalog Admin',
        'email' => 'catalog-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    DB::table('tp_plugins')->insert([
        'id' => 'tentapress/local-only',
        'enabled' => 0,
        'version' => '0.1.0',
        'provider' => 'TentaPress\\LocalOnly\\LocalOnlyServiceProvider',
        'path' => 'plugins/tentapress/local-only',
        'manifest' => json_encode([
            'id' => 'tentapress/local-only',
            'name' => 'Local Only Plugin',
            'description' => 'Only present in local discovery.',
            'version' => '0.1.0',
            'provider' => 'TentaPress\\LocalOnly\\LocalOnlyServiceProvider',
        ], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config()->set('tentapress-system-info.catalog.url', 'https://catalog.example.test/plugins.json');

    Http::fake([
        'https://catalog.example.test/plugins.json' => Http::response('offline', 503),
    ]);

    $this->actingAs($user)
        ->get('/admin/plugins/catalog')
        ->assertOk()
        ->assertSee('Hosted catalog is currently unavailable. Showing local catalog data only.')
        ->assertSee('Local Only Plugin')
        ->assertSee('Local only');
});

it('hides install action for users without manage_plugins capability', function (): void {
    $user = makeUserWithCapability('view_system_info', 'viewer-catalog-install@example.test');

    config()->set('tentapress-system-info.catalog.url', 'https://catalog.example.test/plugins.json');

    Http::fake([
        'https://catalog.example.test/plugins.json' => Http::response([
            'schema_version' => 1,
            'plugins' => [
                [
                    'id' => 'tentapress/static-deploy',
                    'name' => 'Static Deploy',
                    'description' => 'Generate static builds.',
                    'latest_version' => '0.1.7',
                    'package' => 'tentapress/static-deploy',
                ],
            ],
        ], 200),
    ]);

    $this->actingAs($user)
        ->get('/admin/plugins/catalog')
        ->assertOk()
        ->assertSee('You do not have permission to manage plugin installs.')
        ->assertDontSee('Only super administrators can queue installs.');
});

/**
 * @return TpUser
 */
function makeUserWithCapability(string $capability, string $email): TpUser
{
    $user = TpUser::query()->create([
        'name' => 'Catalog Viewer',
        'email' => $email,
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $roleId = (int) DB::table('tp_roles')->insertGetId([
        'name' => 'Catalog Viewer Role',
        'slug' => 'catalog-viewer-'.substr(md5($email), 0, 8),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tp_capabilities')->updateOrInsert(
        ['key' => $capability],
        [
            'label' => ucfirst(str_replace('_', ' ', $capability)),
            'group' => 'System',
            'description' => 'Capability for testing plugin catalog access.',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    DB::table('tp_role_capability')->insert([
        'role_id' => $roleId,
        'capability_key' => $capability,
    ]);

    DB::table('tp_user_roles')->insert([
        'user_id' => (int) $user->getKey(),
        'role_id' => $roleId,
    ]);

    return $user;
}
