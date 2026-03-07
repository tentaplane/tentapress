<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Models\TpGlobalContentUsage;
use TentaPress\GlobalContent\Services\GlobalContentCapabilitySeeder;
use TentaPress\PageEditor\Render\PageDocumentRenderer;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\Users\Models\TpUser;

function refreshTestApplication(): void
{
    \Closure::bind(function (): void {
        $this->refreshApplication();
    }, test(), test()::class)();
}

function registerGlobalContentAutoloader(): void
{
    spl_autoload_register(static function (string $class): void {
        $prefix = 'TentaPress\\GlobalContent\\';

        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (! is_string($relativeClass) || $relativeClass === '') {
            return;
        }

        $path = base_path('plugins/tentapress/global-content/src/'.str_replace('\\', '/', $relativeClass).'.php');
        if (is_file($path)) {
            require_once $path;
        }
    });
}

function bootGlobalContentPlugin(): void
{
    test()->artisan('tp:plugins sync')->assertSuccessful();
    test()->artisan('tp:plugins enable tentapress/global-content')->assertSuccessful();
    test()->beforeApplicationDestroyed(function (): void {
        if (! Schema::hasTable('tp_plugins')) {
            return;
        }

        DB::table('tp_plugins')
            ->where('id', 'tentapress/global-content')
            ->update(['enabled' => 0, 'updated_at' => now()]);

        resolve(PluginRegistry::class)->writeCache();
    });
    registerGlobalContentAutoloader();
    refreshTestApplication();

    test()->artisan('migrate --force')->assertSuccessful();
}

function superAdminUser(string $email = 'global-content-admin@example.test'): TpUser
{
    return TpUser::query()->create([
        'name' => 'Global Content Admin',
        'email' => $email,
        'password' => 'secret',
        'is_super_admin' => true,
    ]);
}

function regularUser(string $email = 'global-content-user@example.test'): TpUser
{
    return TpUser::query()->create([
        'name' => 'Global Content User',
        'email' => $email,
        'password' => 'secret',
        'is_super_admin' => false,
    ]);
}

function globalContentBlocks(string $content): array
{
    return [
        [
            'type' => 'blocks/content',
            'version' => 2,
            'props' => [
                'content' => $content,
                'width' => 'normal',
                'alignment' => 'left',
                'background' => 'white',
            ],
        ],
    ];
}

function referenceBlock(int $id, string $label = ''): array
{
    return [
        'type' => 'tentapress/global-content/reference',
        'version' => 1,
        'props' => [
            'global_content_id' => (string) $id,
            'global_content_label' => $label,
        ],
    ];
}

it('redirects guests from global content admin routes to login', function (): void {
    bootGlobalContentPlugin();

    $this->get('/admin/global-content')->assertRedirect('/admin/login');
    $this->get('/admin/global-content/new')->assertRedirect('/admin/login');
});

it('forbids authenticated users without the plugin capability', function (): void {
    bootGlobalContentPlugin();

    $user = regularUser();

    $this->actingAs($user)
        ->get('/admin/global-content')
        ->assertForbidden();
});

it('shows the plugin menu when enabled and allows full admin CRUD', function (): void {
    bootGlobalContentPlugin();

    $admin = superAdminUser();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Global Content');

    $this->actingAs($admin)
        ->post('/admin/global-content', [
            'title' => 'Header Banner',
            'slug' => 'header-banner',
            'kind' => 'template_part',
            'status' => 'published',
            'description' => 'Shown above the site header.',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode(globalContentBlocks('Welcome everywhere'), JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHas('tp_notice_success', 'Global content created.');

    $content = TpGlobalContent::query()->firstOrFail();

    expect((string) $content->slug)->toBe('header-banner');
    expect((string) $content->status)->toBe('published');

    $this->actingAs($admin)
        ->put('/admin/global-content/'.$content->id, [
            'title' => 'Header Banner Updated',
            'slug' => 'header-banner',
            'kind' => 'template_part',
            'status' => 'draft',
            'description' => 'Updated description.',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode(globalContentBlocks('Updated everywhere'), JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHas('tp_notice_success', 'Global content updated.');

    expect((string) $content->fresh()->title)->toBe('Header Banner Updated');
    expect((string) $content->fresh()->status)->toBe('draft');

    $this->actingAs($admin)
        ->delete('/admin/global-content/'.$content->id)
        ->assertSessionHas('tp_notice_success', 'Global content deleted.');

    expect(TpGlobalContent::query()->count())->toBe(0);
});

it('seeds the plugin capability and grants editor-role access when assigned', function (): void {
    bootGlobalContentPlugin();

    DB::table('tp_roles')->insert([
        ['name' => 'Administrator', 'slug' => 'administrator', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Editor', 'slug' => 'editor', 'created_at' => now(), 'updated_at' => now()],
    ]);

    resolve(GlobalContentCapabilitySeeder::class)->run();

    expect(DB::table('tp_capabilities')->where('key', 'manage_global_content')->exists())->toBeTrue();
    expect(DB::table('tp_role_capability')->where('capability_key', 'manage_global_content')->count())->toBe(2);

    $editor = regularUser('global-content-editor@example.test');
    $editorRoleId = (int) DB::table('tp_roles')->where('slug', 'editor')->value('id');

    DB::table('tp_user_roles')->insert([
        'user_id' => (int) $editor->id,
        'role_id' => $editorRoleId,
    ]);

    $editor->refresh();

    $this->actingAs($editor)
        ->get('/admin/global-content')
        ->assertOk();
});

it('renders published referenced content, supports detaching, and indexes page and post usage', function (): void {
    bootGlobalContentPlugin();

    $admin = superAdminUser('global-content-render-admin@example.test');

    $content = TpGlobalContent::query()->create([
        'title' => 'Reusable Promo',
        'slug' => 'reusable-promo',
        'kind' => 'section',
        'status' => 'published',
        'editor_driver' => 'blocks',
        'blocks' => globalContentBlocks('Promo message'),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Landing',
        'slug' => 'landing',
        'status' => 'draft',
        'editor_driver' => 'blocks',
        'blocks' => [referenceBlock((int) $content->id, 'Reusable Promo')],
    ]);

    $post = TpPost::query()->create([
        'title' => 'Announcement',
        'slug' => 'announcement',
        'status' => 'draft',
        'editor_driver' => 'blocks',
        'blocks' => [referenceBlock((int) $content->id, 'Reusable Promo')],
    ]);

    $renderBlocks = resolve('tp.blocks.render');
    $pageHtml = $renderBlocks($page->blocks);

    expect($pageHtml)->toContain('Promo message');
    expect(TpGlobalContentUsage::query()->where('owner_type', 'page')->where('owner_id', $page->id)->exists())->toBeTrue();
    expect(TpGlobalContentUsage::query()->where('owner_type', 'post')->where('owner_id', $post->id)->exists())->toBeTrue();

    $pageEditorHtml = resolve(PageDocumentRenderer::class)->render([
        'time' => 0,
        'version' => '2.28.0',
        'blocks' => [
            [
                'type' => 'globalContent',
                'data' => [
                    'global_content_id' => (int) $content->id,
                    'label' => 'Reusable Promo',
                ],
            ],
        ],
    ]);

    expect($pageEditorHtml)->toContain('Promo message');

    $this->actingAs($admin)
        ->postJson('/admin/global-content/detach', [
            'global_content_id' => $content->id,
        ])
        ->assertOk()
        ->assertJsonPath('blocks.0.type', 'blocks/content')
        ->assertJsonPath('blocks.0.props.content', 'Promo message');

    $page->update([
        'blocks' => [],
    ]);

    expect(TpGlobalContentUsage::query()->where('owner_type', 'page')->where('owner_id', $page->id)->exists())->toBeFalse();
});

it('rejects recursive references and fails safe for missing or unpublished content', function (): void {
    bootGlobalContentPlugin();

    $admin = superAdminUser('global-content-cycle-admin@example.test');

    $first = TpGlobalContent::query()->create([
        'title' => 'First',
        'slug' => 'first',
        'kind' => 'section',
        'status' => 'published',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $second = TpGlobalContent::query()->create([
        'title' => 'Second',
        'slug' => 'second',
        'kind' => 'section',
        'status' => 'published',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->put('/admin/global-content/'.$first->id, [
            'title' => 'First',
            'slug' => 'first',
            'kind' => 'section',
            'status' => 'published',
            'description' => '',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode([referenceBlock((int) $second->id, 'Second')], JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHas('tp_notice_success', 'Global content updated.');

    $this->actingAs($admin)
        ->from('/admin/global-content/'.$second->id.'/edit')
        ->put('/admin/global-content/'.$second->id, [
            'title' => 'Second',
            'slug' => 'second',
            'kind' => 'section',
            'status' => 'published',
            'description' => '',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode([referenceBlock((int) $first->id, 'First')], JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect('/admin/global-content/'.$second->id.'/edit')
        ->assertSessionHasErrors('blocks_json');

    $draft = TpGlobalContent::query()->create([
        'title' => 'Draft Promo',
        'slug' => 'draft-promo',
        'kind' => 'section',
        'status' => 'draft',
        'editor_driver' => 'blocks',
        'blocks' => globalContentBlocks('Hidden draft'),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $renderer = resolve('tp.blocks.render');

    expect($renderer([referenceBlock((int) $draft->id, 'Draft Promo')]))->toBe('');
    expect($renderer([referenceBlock(999999, 'Missing')]))->toBe('');
});

it('renders the theme helper for published template parts only', function (): void {
    bootGlobalContentPlugin();

    $admin = superAdminUser('global-content-theme-admin@example.test');

    TpGlobalContent::query()->create([
        'title' => 'Header Banner',
        'slug' => 'header-banner',
        'kind' => 'template_part',
        'status' => 'published',
        'editor_driver' => 'blocks',
        'blocks' => globalContentBlocks('Theme banner'),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    TpGlobalContent::query()->create([
        'title' => 'Draft Banner',
        'slug' => 'draft-banner',
        'kind' => 'template_part',
        'status' => 'draft',
        'editor_driver' => 'blocks',
        'blocks' => globalContentBlocks('Draft theme banner'),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    expect(Blade::render("@tpGlobalContent('header-banner')"))->toContain('Theme banner');
    expect(Blade::render("@tpGlobalContent('draft-banner')"))->toBe('');
    expect(Blade::render("@tpGlobalContent('missing-banner')"))->toBe('');
});

it('removes routes, menu entries, and block registration when the plugin is disabled', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/global-content --force')->assertSuccessful();
    refreshTestApplication();
    $this->artisan('migrate --force')->assertSuccessful();

    expect(Route::has('tp.global-content.index'))->toBeFalse();
    expect(resolve(BlockRegistry::class)->get('tentapress/global-content/reference'))->toBeNull();

    $admin = TpUser::query()->create([
        'name' => 'Disabled Admin',
        'email' => 'global-content-disabled-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('/admin/global-content');
});
