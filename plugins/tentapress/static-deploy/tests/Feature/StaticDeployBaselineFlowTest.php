<?php

declare(strict_types=1);

use TentaPress\Settings\Services\SettingsStore;
use TentaPress\StaticDeploy\StaticDeployServiceProvider;
use TentaPress\Users\Models\TpUser;

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
        ->assertSee('Replacement rules');
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
