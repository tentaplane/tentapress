<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\StaticDeploy\StaticDeployServiceProvider;
use TentaPress\Users\Models\TpUser;

function staticDeployArtifactsDir(): string
{
    $base = 'tp-static';
    $token = (string) getenv('TEST_TOKEN');

    if ($token !== '') {
        $base .= '-' . $token;
    }

    return storage_path('app/' . $base);
}

function registerStaticDeployProviderForEdgeCases(): void
{
    app()->register(StaticDeployServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('denies static deploy access to non-super-admin users', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $user = TpUser::query()->create([
        'name' => 'Static Deploy User',
        'email' => 'static-deploy-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/static-deploy')
        ->assertForbidden();
});

it('validates static deploy generate options as booleans', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/static-deploy')
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => 'yes',
            'include_robots' => 'no',
            'compress_html' => 'invalid',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHasErrors(['include_favicon', 'include_robots', 'compress_html']);
});

it('validates static deploy replacement rules as JSON rule objects', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-rules-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/static-deploy')
        ->post('/admin/static-deploy/rules', [
            'replacement_rules_json' => '{"find":"broken"}',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHasErrors(['replacement_rules_json']);
});

it('allows a super admin to reset saved replacement rules', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-rules-reset@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    resolve(SettingsStore::class)->set('static_deploy.find_replace_rules', json_encode([
        [
            'find' => 'from',
            'replace' => 'to',
            'files' => ['*.html'],
        ],
    ], JSON_THROW_ON_ERROR), true);

    $this->actingAs($admin)
        ->post('/admin/static-deploy/rules', [
            'rules_action' => 'reset',
            'replacement_rules_json' => '{"ignored":true}',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success', 'Replacement rules reset.');

    expect(json_decode((string) resolve(SettingsStore::class)->get('static_deploy.find_replace_rules'), true, 512, JSON_THROW_ON_ERROR))
        ->toBe([]);
});

it('returns not found when no static export archive exists', function (): void {
    registerStaticDeployProviderForEdgeCases();
    File::deleteDirectory(staticDeployArtifactsDir());

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-no-archive@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/static-deploy/download')
        ->assertNotFound();
});

it('returns not found when a requested stored export archive does not exist', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-missing-specific-archive@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/static-deploy/download/20260228-235959')
        ->assertNotFound();
});

it('generates a build even when the pages table is unavailable', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-missing-pages-table@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    Schema::dropIfExists('tp_pages');

    $this->actingAs($admin)
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => '0',
            'include_robots' => '0',
            'compress_html' => '0',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success');
});

it('applies saved replacement rules before the static export is zipped', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-replacements@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    resolve(SettingsStore::class)->set('static_deploy.find_replace_rules', json_encode([
        [
            'find' => '<html',
            'replace' => '<html data-static-export="1"',
            'files' => ['*.html'],
        ],
    ], JSON_THROW_ON_ERROR), true);

    $this->actingAs($admin)
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => '0',
            'include_robots' => '0',
            'compress_html' => '0',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success');

    $last = json_decode(File::get(staticDeployArtifactsDir() . '/last.json'), true, 512, JSON_THROW_ON_ERROR);
    $exportedHtmlFiles = collect(File::allFiles((string) $last['build_dir']))
        ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'html')
        ->map(fn (\SplFileInfo $file): string => File::get($file->getPathname()))
        ->values();

    expect($exportedHtmlFiles)->not->toBeEmpty()
        ->and($exportedHtmlFiles->contains(fn (string $contents): bool => str_contains($contents, '<html data-static-export="1"')))->toBeTrue()
        ->and($last['replacement_rules_applied'])->toBe(1)
        ->and($last['replacement_files_updated'])->toBeGreaterThanOrEqual(1)
        ->and($last['replacement_matches'])->toBeGreaterThanOrEqual(1);
});
