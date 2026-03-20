<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\RevisionsServiceProvider;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Models\TpWorkflowItem;
use TentaPress\Workflow\WorkflowServiceProvider;

beforeEach(function (): void {
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/workflow')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/revisions')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('tp:plugins sync')->assertSuccessful();
});

function enableWorkflowPlugin(): void
{
    test()->artisan('tp:plugins enable tentapress/revisions')->assertSuccessful();
    test()->artisan('tp:plugins enable tentapress/workflow')->assertSuccessful();
    app()->register(RevisionsServiceProvider::class);
    app()->register(WorkflowServiceProvider::class);
    test()->artisan('migrate', ['--force' => true])->assertSuccessful();
}

function makeWorkflowAdmin(): TpUser
{
    return TpUser::query()->create([
        'name' => 'Workflow Admin',
        'email' => 'workflow-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);
}

/**
 * @param array<int,string> $capabilities
 */
function grantCapabilities(TpUser $user, array $capabilities): void
{
    $roleId = DB::table('tp_roles')->insertGetId([
        'slug' => 'workflow-role-'.$user->id,
        'name' => 'Workflow Role '.$user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tp_user_roles')->insert([
        'user_id' => (int) $user->id,
        'role_id' => $roleId,
    ]);

    foreach ($capabilities as $capability) {
        DB::table('tp_capabilities')->updateOrInsert(
            ['key' => $capability],
            [
                'label' => ucwords(str_replace('_', ' ', $capability)),
                'group' => 'Workflow Test',
                'description' => 'Test capability.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('tp_role_capability')->insert([
            'role_id' => $roleId,
            'capability_key' => $capability,
        ]);
    }
}

it('redirects guests from the workflow queue to login when the plugin is enabled', function (): void {
    enableWorkflowPlugin();

    $this->get('/admin/workflow')->assertRedirect('/admin/login');
});

it('keeps workflow routes and edit UI absent when the plugin is not enabled', function (): void {
    $admin = makeWorkflowAdmin();

    $page = TpPage::query()->create([
        'title' => 'Disabled Workflow Page',
        'slug' => 'disabled-workflow-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $this->actingAs($admin)->get('/admin/workflow')->assertNotFound();

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertDontSee('Save workflow assignments')
        ->assertDontSee('Submit for review');
});

it('denies approval to a user without workflow approval capability', function (): void {
    enableWorkflowPlugin();

    $admin = makeWorkflowAdmin();

    $editor = TpUser::query()->create([
        'name' => 'Workflow Editor',
        'email' => 'workflow-editor@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    grantCapabilities($editor, ['manage_pages']);

    $page = TpPage::query()->create([
        'title' => 'Approval Guard Page',
        'slug' => 'approval-guard-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/assign', [
        'owner_user_id' => $editor->id,
        'reviewer_user_id' => null,
        'approver_user_id' => $admin->id,
    ])->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $this->actingAs($editor)->post('/admin/workflow/pages/'.$page->id.'/approve')->assertForbidden();
});

it('moves a page through assignment, review, approval, and publish', function (): void {
    enableWorkflowPlugin();

    $admin = makeWorkflowAdmin();

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Workflow Happy Path',
        'slug' => 'workflow-happy-path',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[]',
        'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
    ])->assertRedirect();

    $page = TpPage::query()->where('slug', 'workflow-happy-path')->firstOrFail();

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/assign', [
        'owner_user_id' => $admin->id,
        'reviewer_user_id' => null,
        'approver_user_id' => $admin->id,
    ])->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/submit')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/approve')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/publish')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $page->refresh();
    $workflowItem = TpWorkflowItem::query()
        ->where('resource_type', 'pages')
        ->where('resource_id', $page->id)
        ->firstOrFail();

    expect((string) $page->status)->toBe('published');
    expect((string) $workflowItem->editorial_state)->toBe('approved');

    $this->actingAs($admin)
        ->get('/admin/workflow')
        ->assertOk()
        ->assertSee('Workflow Happy Path');
});

it('stages edits to a published page as a workflow working copy', function (): void {
    enableWorkflowPlugin();

    $admin = makeWorkflowAdmin();

    $page = TpPage::query()->create([
        'title' => 'Live Workflow Page',
        'slug' => 'live-workflow-page',
        'status' => 'published',
        'layout' => 'default',
        'blocks' => [],
        'published_at' => now()->subMinute(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->actingAs($admin)->post('/admin/workflow/pages/'.$page->id.'/assign', [
        'owner_user_id' => $admin->id,
        'reviewer_user_id' => null,
        'approver_user_id' => $admin->id,
    ])->assertRedirect('/admin/pages/'.$page->id.'/edit');

    $this->actingAs($admin)->put('/admin/pages/'.$page->id, [
        'title' => 'Live Workflow Page Updated',
        'slug' => 'live-workflow-page',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[]',
        'page_doc_json' => '{"time":1,"blocks":[{"id":"wf","type":"paragraph","data":{"text":"Pending"}}],"version":"2.28.0"}',
    ])->assertRedirect('/admin/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Changes saved to the workflow draft. Approve and publish them when ready.');

    $page->refresh();
    $workflowItem = TpWorkflowItem::query()
        ->where('resource_type', 'pages')
        ->where('resource_id', $page->id)
        ->firstOrFail();

    expect((string) $page->title)->toBe('Live Workflow Page');
    expect((int) ($workflowItem->pending_revision_id ?? 0))->toBeGreaterThan(0);

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertSee('Live Workflow Page Updated')
        ->assertSee('Staged from revisions');
});

it('publishes approved scheduled posts with the workflow command', function (): void {
    enableWorkflowPlugin();

    $admin = makeWorkflowAdmin();

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Workflow Scheduled Post',
        'slug' => 'workflow-scheduled-post',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[]',
        'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
        'author_id' => $admin->id,
        'published_at' => '',
    ])->assertRedirect();

    $post = TpPost::query()->where('slug', 'workflow-scheduled-post')->firstOrFail();

    $this->actingAs($admin)->post('/admin/workflow/posts/'.$post->id.'/assign', [
        'owner_user_id' => $admin->id,
        'reviewer_user_id' => null,
        'approver_user_id' => $admin->id,
    ])->assertRedirect('/admin/posts/'.$post->id.'/edit');

    $this->actingAs($admin)->post('/admin/workflow/posts/'.$post->id.'/submit')->assertRedirect('/admin/posts/'.$post->id.'/edit');
    $this->actingAs($admin)->post('/admin/workflow/posts/'.$post->id.'/approve')->assertRedirect('/admin/posts/'.$post->id.'/edit');
    $this->actingAs($admin)->post('/admin/workflow/posts/'.$post->id.'/schedule', [
        'scheduled_publish_at' => now()->addMinute()->format('Y-m-d H:i:s'),
    ])->assertRedirect('/admin/posts/'.$post->id.'/edit');

    $this->travelTo(now()->addMinutes(2));

    $this->artisan('tp:workflow:publish-scheduled')
        ->expectsOutput('Published 1 scheduled workflow item(s).')
        ->assertSuccessful();

    $post->refresh();

    expect((string) $post->status)->toBe('published');
});
