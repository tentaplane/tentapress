<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TentaPress\ContentTypes\Services\ContentEntryRenderer;
use TentaPress\ContentTypes\Services\ContentTypeFormDataFactory;
use TentaPress\ContentTypes\Http\Public\ArchiveController as PublicArchiveController;
use TentaPress\ContentTypes\Http\Public\ShowController as PublicShowController;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Models\TpContentTypeField;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/content-types')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('tp:plugins sync')->assertSuccessful();
});

function enableContentTypesPlugin(): void
{
    test()->artisan('tp:plugins enable tentapress/blocks')->assertSuccessful();
    test()->artisan('tp:plugins enable tentapress/content-types')->assertSuccessful();
    test()->refreshApplication();
    test()->artisan('migrate', ['--force' => true])->assertSuccessful();
}

function makeAdminUser(): TpUser
{
    return TpUser::query()->create([
        'name' => 'Content Admin',
        'email' => 'content-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);
}

function makeBasicUser(): TpUser
{
    return TpUser::query()->create([
        'name' => 'Content Author',
        'email' => 'content-author@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);
}

function createContentType(array $attributes = []): TpContentType
{
    /** @var TpContentType $contentType */
    $contentType = TpContentType::query()->create(array_merge([
        'key' => 'case-studies',
        'singular_label' => 'Case Study',
        'plural_label' => 'Case Studies',
        'description' => 'Customer stories.',
        'base_path' => 'case-studies',
        'default_layout' => 'default',
        'default_editor_driver' => 'blocks',
        'archive_enabled' => true,
        'api_visibility' => 'public',
    ], $attributes));

    return $contentType;
}

function addField(TpContentType $contentType, array $attributes = []): TpContentTypeField
{
    /** @var TpContentTypeField $field */
    $field = $contentType->fields()->create(array_merge([
        'key' => 'status',
        'label' => 'Status',
        'field_type' => 'select',
        'sort_order' => 1,
        'required' => true,
        'config' => [
            'options' => [
                ['value' => 'planned', 'label' => 'Planned'],
                ['value' => 'live', 'label' => 'Live'],
            ],
        ],
    ], $attributes));

    return $field;
}

it('keeps routes absent when disabled and redirects guests when enabled', function (): void {
    $this->get('/admin/content-types')->assertNotFound();

    enableContentTypesPlugin();

    $this->get('/admin/content-types')->assertRedirect('/admin/login');
});

it('creates and updates content types through the admin flow', function (): void {
    enableContentTypesPlugin();

    $admin = makeAdminUser();

    $this->actingAs($admin)
        ->post('/admin/content-types', [
            'key' => 'events',
            'singular_label' => 'Event',
            'plural_label' => 'Events',
            'description' => 'Upcoming events.',
            'base_path' => 'events',
            'default_layout' => 'default',
            'default_editor_driver' => 'blocks',
            'archive_enabled' => '1',
            'api_visibility' => 'public',
            'fields_json' => json_encode([
                [
                    'key' => 'event_date',
                    'label' => 'Event date',
                    'field_type' => 'date_time',
                    'required' => true,
                    'config' => [],
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'field_type' => 'select',
                    'required' => false,
                    'config' => [
                        'options' => [
                            ['value' => 'planned', 'label' => 'Planned'],
                            ['value' => 'live', 'label' => 'Live'],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect('/admin/content-types/1/edit')
        ->assertSessionHas('tp_notice_success', 'Content type created.');

    $contentType = TpContentType::query()->where('key', 'events')->firstOrFail();

    expect($contentType->base_path)->toBe('events');
    expect($contentType->fields()->count())->toBe(2);

    $this->actingAs($admin)
        ->put('/admin/content-types/'.$contentType->id, [
            'key' => 'events',
            'singular_label' => 'Event',
            'plural_label' => 'Events',
            'description' => 'Updated event records.',
            'base_path' => 'events',
            'default_layout' => 'landing',
            'default_editor_driver' => 'blocks',
            'archive_enabled' => '0',
            'api_visibility' => 'disabled',
            'fields_json' => json_encode([
                [
                    'key' => 'event_date',
                    'label' => 'Event date',
                    'field_type' => 'date_time',
                    'required' => true,
                    'config' => [],
                ],
            ], JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Content type updated.');

    $contentType->refresh();

    expect($contentType->default_layout)->toBe('landing');
    expect($contentType->archive_enabled)->toBeFalse();
    expect($contentType->api_visibility)->toBe('disabled');
    expect($contentType->fields()->count())->toBe(1);
});

it('rejects reserved base paths and denies unauthorised access', function (): void {
    enableContentTypesPlugin();

    $author = makeBasicUser();

    $this->actingAs($author)->get('/admin/content-types')->assertForbidden();

    $admin = makeAdminUser();

    $this->actingAs($admin)
        ->post('/admin/content-types', [
            'key' => 'system-pages',
            'singular_label' => 'System Page',
            'plural_label' => 'System Pages',
            'description' => '',
            'base_path' => 'admin',
            'default_layout' => 'default',
            'default_editor_driver' => 'blocks',
            'archive_enabled' => '1',
            'api_visibility' => 'public',
            'fields_json' => '[]',
        ])
        ->assertSessionHasErrors('fields_json');
});

it('creates, updates, and validates content entries with structured fields', function (): void {
    enableContentTypesPlugin();

    $admin = makeAdminUser();
    $contentType = createContentType();

    addField($contentType);
    addField($contentType, [
        'key' => 'related_case_study',
        'label' => 'Related case study',
        'field_type' => 'relation',
        'sort_order' => 2,
        'required' => false,
        'config' => ['allowed_type_keys' => ['case-studies']],
    ]);

    $related = TpContentEntry::query()->create([
        'content_type_id' => $contentType->id,
        'title' => 'Existing case study',
        'slug' => 'existing-case-study',
        'status' => 'draft',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'field_values' => [],
    ]);

    $this->actingAs($admin)
        ->post('/admin/content-types/'.$contentType->id.'/entries', [
            'title' => 'Launch story',
            'slug' => 'launch-story',
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode([
                ['type' => 'blocks/content', 'props' => ['content' => 'Launch body']],
            ], JSON_THROW_ON_ERROR),
            'page_doc_json' => 'null',
            'field_values' => [
                'status' => 'live',
                'related_case_study' => (string) $related->id,
            ],
        ])
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/entries/2/edit')
        ->assertSessionHas('tp_notice_success', 'Content entry created.');

    $entry = TpContentEntry::query()->where('slug', 'launch-story')->firstOrFail();

    expect($entry->field_values)->toMatchArray([
        'status' => 'live',
        'related_case_study' => 'content-types:'.$related->id,
    ]);

    $this->actingAs($admin)
        ->put('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id, [
            'title' => 'Launch story updated',
            'slug' => 'launch-story',
            'layout' => 'landing',
            'editor_driver' => 'blocks',
            'blocks_json' => json_encode([
                ['type' => 'blocks/content', 'props' => ['content' => 'Updated launch body']],
            ], JSON_THROW_ON_ERROR),
            'page_doc_json' => 'null',
            'field_values' => [
                'status' => 'planned',
                'related_case_study' => (string) $related->id,
            ],
        ])
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Content entry updated.');

    $entry->refresh();

    expect($entry->title)->toBe('Launch story updated');
    expect($entry->layout)->toBe('landing');
    expect($entry->field_values)->toMatchArray([
        'status' => 'planned',
        'related_case_study' => 'content-types:'.$related->id,
    ]);

    $this->actingAs($admin)
        ->post('/admin/content-types/'.$contentType->id.'/entries', [
            'title' => 'Broken relation',
            'slug' => 'broken-relation',
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => 'null',
            'field_values' => [
                'status' => 'live',
                'related_case_study' => '999999',
            ],
        ])
        ->assertSessionHasErrors('field_values');
});

it('supports optional page and post references without making them required', function (): void {
    enableContentTypesPlugin();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();

    $admin = makeAdminUser();
    $contentType = createContentType();
    addField($contentType);

    addField($contentType, [
        'key' => 'featured_page',
        'label' => 'Featured page',
        'field_type' => 'relation',
        'sort_order' => 2,
        'required' => false,
        'config' => ['allowed_sources' => ['pages']],
    ]);

    addField($contentType, [
        'key' => 'featured_post',
        'label' => 'Featured post',
        'field_type' => 'relation',
        'sort_order' => 3,
        'required' => false,
        'config' => ['allowed_sources' => ['posts']],
    ]);

    $page = TpPage::query()->create([
        'title' => 'About TentaPress',
        'slug' => 'about',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [],
    ]);

    $post = TpPost::query()->create([
        'title' => 'Launch Notes',
        'slug' => 'launch-notes',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [],
    ]);

    $this->actingAs($admin)
        ->get('/admin/content-types/'.$contentType->id.'/entries/new')
        ->assertOk()
        ->assertSee('About TentaPress')
        ->assertSee('Launch Notes');

    $this->actingAs($admin)
        ->post('/admin/content-types/'.$contentType->id.'/entries', [
            'title' => 'Integration record',
            'slug' => 'integration-record',
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
            'field_values' => [
                'status' => 'live',
                'featured_page' => 'pages:'.$page->id,
                'featured_post' => 'posts:'.$post->id,
            ],
        ])
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/entries/1/edit')
        ->assertSessionHas('tp_notice_success', 'Content entry created.');

    $entry = TpContentEntry::query()->where('slug', 'integration-record')->firstOrFail();

    expect($entry->field_values)->toMatchArray([
        'status' => 'live',
        'featured_page' => 'pages:'.$page->id,
        'featured_post' => 'posts:'.$post->id,
    ]);
});

it('loads first-party block definitions in the content entry editor', function (): void {
    enableContentTypesPlugin();

    $admin = makeAdminUser();
    $contentType = createContentType();
    $definitions = resolve(ContentTypeFormDataFactory::class)->blockDefinitions();

    $this->actingAs($admin)
        ->get('/admin/content-types/'.$contentType->id.'/entries/new')
        ->assertOk()
        ->assertSee('Add block');

    expect(collect($definitions)->pluck('type')->all())->toContain('blocks/content');
});

it('publishes, unpublishes, and renders public routes correctly', function (): void {
    enableContentTypesPlugin();

    $admin = makeAdminUser();
    $contentType = createContentType([
        'key' => 'team-members',
        'singular_label' => 'Team Member',
        'plural_label' => 'Team Members',
        'base_path' => 'team',
        'archive_enabled' => true,
    ]);

    addField($contentType, [
        'key' => 'role_title',
        'label' => 'Role title',
        'field_type' => 'text',
        'sort_order' => 1,
        'required' => false,
        'config' => [],
    ]);

    $entry = TpContentEntry::query()->create([
        'content_type_id' => $contentType->id,
        'title' => 'Alex Example',
        'slug' => 'alex-example',
        'status' => 'draft',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            ['type' => 'blocks/content', 'props' => ['content' => 'Profile body']],
        ],
        'field_values' => ['role_title' => 'Designer'],
    ]);

    $archiveResponse = resolve(PublicArchiveController::class)->__invoke('team-members');
    expect($archiveResponse->render())->toContain('No team members have been published yet.');

    expect(fn () => resolve(PublicShowController::class)->__invoke('team-members', 'alex-example', resolve(ContentEntryRenderer::class)))
        ->toThrow(ModelNotFoundException::class);

    $this->actingAs($admin)
        ->post('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id.'/publish')
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Content entry published.');

    $entry->refresh();

    expect($entry->status)->toBe('published');
    expect($entry->published_at)->not->toBeNull();

    $archiveResponse = resolve(PublicArchiveController::class)->__invoke('team-members');
    expect($archiveResponse->render())->toContain('Alex Example');

    $showResponse = resolve(PublicShowController::class)->__invoke('team-members', 'alex-example', resolve(ContentEntryRenderer::class));
    expect($showResponse->getContent())->toContain('Alex Example');
    expect($showResponse->getContent())->toContain('Designer');

    $this->actingAs($admin)
        ->post('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id.'/unpublish')
        ->assertRedirect('/admin/content-types/'.$contentType->id.'/entries/'.$entry->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Content entry moved back to draft.');

    expect(fn () => resolve(PublicShowController::class)->__invoke('team-members', 'alex-example', resolve(ContentEntryRenderer::class)))
        ->toThrow(ModelNotFoundException::class);
});

it('honours archive and api visibility settings', function (): void {
    enableContentTypesPlugin();

    $publicType = createContentType([
        'key' => 'services',
        'singular_label' => 'Service',
        'plural_label' => 'Services',
        'base_path' => 'services',
        'archive_enabled' => false,
        'api_visibility' => 'public',
    ]);

    $hiddenType = createContentType([
        'key' => 'internal-updates',
        'singular_label' => 'Internal Update',
        'plural_label' => 'Internal Updates',
        'base_path' => 'internal-updates',
        'api_visibility' => 'disabled',
    ]);

    TpContentEntry::query()->create([
        'content_type_id' => $publicType->id,
        'title' => 'Strategy Workshop',
        'slug' => 'strategy-workshop',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'field_values' => ['status' => 'live'],
        'published_at' => now()->subMinute(),
    ]);

    TpContentEntry::query()->create([
        'content_type_id' => $hiddenType->id,
        'title' => 'Internal Memo',
        'slug' => 'internal-memo',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'field_values' => [],
        'published_at' => now()->subMinute(),
    ]);

    $this->get('/services')->assertNotFound();

    $typesResponse = $this->getJson('/api/v1/content-types');
    $typesResponse->assertOk()
        ->assertJsonPath('meta.count', 1)
        ->assertJsonPath('data.0.key', 'services');

    $archiveResponse = $this->getJson('/api/v1/content-types/services');
    $archiveResponse->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'strategy-workshop')
        ->assertJsonPath('type.key', 'services');

    $showResponse = $this->getJson('/api/v1/content-types/services/strategy-workshop');
    $showResponse->assertOk()
        ->assertJsonPath('data.permalink', '/services/strategy-workshop')
        ->assertJsonPath('type.base_path', 'services');

    $this->getJson('/api/v1/content-types/internal-updates')->assertNotFound();
});

it('creates plugin-owned tables when enabled', function (): void {
    enableContentTypesPlugin();

    expect(Schema::hasTable('tp_content_types'))->toBeTrue();
    expect(Schema::hasTable('tp_content_type_fields'))->toBeTrue();
    expect(Schema::hasTable('tp_content_entries'))->toBeTrue();
});
