<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\StaticDeploy\StaticDeployServiceProvider;
use TentaPress\Users\Models\TpUser;

function staticDeployBaselineArtifactsDir(): string
{
    $base = 'tp-static';
    $token = (string) getenv('TEST_TOKEN');

    if ($token !== '') {
        $base .= '-' . $token;
    }

    return storage_path('app/' . $base);
}

function registerStaticDeployProvider(): void
{
    app()->register(StaticDeployServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('redirects guests from static deploy admin routes to login', function (): void {
    registerStaticDeployProvider();

    $this->get('/admin/static-deploy')->assertRedirect('/admin/login');
    $this->post('/admin/static-deploy/rules')->assertRedirect('/admin/login');
    $this->post('/admin/static-deploy/generate')->assertRedirect('/admin/login');
    $this->get('/admin/static-deploy/download')->assertRedirect('/admin/login');
    $this->get('/admin/static-deploy/download/20260228-121500')->assertRedirect('/admin/login');
});

it('allows a super admin to view static deploy index', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/static-deploy')
        ->assertOk()
        ->assertViewIs('tentapress-static-deploy::index')
        ->assertSee('Replacement rules')
        ->assertSee('Stored exports');
});

it('lists stored exports for a super admin', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-export-history@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    File::ensureDirectoryExists(staticDeployBaselineArtifactsDir() . '/exports');
    File::put(staticDeployBaselineArtifactsDir() . '/exports/static-20260228-101500.zip', 'older-export');
    File::put(staticDeployBaselineArtifactsDir() . '/exports/static-20260228-111500.zip', 'latest-export');
    File::put(staticDeployBaselineArtifactsDir() . '/last.json', json_encode([
        'timestamp' => '20260228-111500',
        'zip_path' => staticDeployBaselineArtifactsDir() . '/exports/static-20260228-111500.zip',
    ], JSON_THROW_ON_ERROR));

    $this->actingAs($admin)
        ->get('/admin/static-deploy')
        ->assertOk()
        ->assertSee('static-20260228-101500.zip')
        ->assertSee('static-20260228-111500.zip')
        ->assertSee('Latest');
});

it('allows a super admin to save static deploy replacement rules', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-rules@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $payload = json_encode([
        [
            'find' => '<html',
            'replace' => '<html data-static-export="1"',
            'files' => ['*.html'],
        ],
    ], JSON_THROW_ON_ERROR);

    $this->actingAs($admin)
        ->post('/admin/static-deploy/rules', [
            'replacement_rules_json' => $payload,
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success', 'Static deploy replacement rules saved.');

    expect(json_decode((string) resolve(SettingsStore::class)->get('static_deploy.find_replace_rules'), true, 512, JSON_THROW_ON_ERROR))
        ->toBe(json_decode($payload, true, 512, JSON_THROW_ON_ERROR));
});

it('allows a super admin to load the example replacement rules payload', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-rules-example@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/static-deploy/rules', [
            'rules_action' => 'load_example',
            'replacement_rules_json' => '[]',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success', 'Example replacement rules loaded.');

    $savedRules = json_decode((string) resolve(SettingsStore::class)->get('static_deploy.find_replace_rules'), true, 512, JSON_THROW_ON_ERROR);

    expect($savedRules)->toBeArray()
        ->and($savedRules)->toHaveCount(2)
        ->and($savedRules[0]['find'])->toBe('<html');
});

it('allows a super admin to generate and download a static export', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-export@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => '1',
            'include_robots' => '1',
            'compress_html' => '0',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success');

    $this->actingAs($admin)
        ->get('/admin/static-deploy/download')
        ->assertOk();
});

it('allows a super admin to download a specific stored export', function (): void {
    registerStaticDeployProvider();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-export-specific@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    File::ensureDirectoryExists(staticDeployBaselineArtifactsDir() . '/exports');
    File::put(staticDeployBaselineArtifactsDir() . '/exports/static-20260228-121500.zip', 'specific-export');

    $this->actingAs($admin)
        ->get('/admin/static-deploy/download/20260228-121500')
        ->assertDownload('static-20260228-121500.zip');
});
