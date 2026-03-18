<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Users\Models\TpUser;

function refreshMarketingTestApplication(): void
{
    \Closure::bind(function (): void {
        $this->refreshApplication();
    }, test(), test()::class)();
}

function bootMarketingPlugin(): void
{
    test()->artisan('tp:plugins sync')->assertSuccessful();

    foreach ([
        'tentapress/admin-shell',
        'tentapress/settings',
        'tentapress/users',
        'tentapress/marketing',
    ] as $pluginId) {
        test()->artisan('tp:plugins enable '.$pluginId.' --force')->assertSuccessful();
    }

    refreshMarketingTestApplication();
    test()->artisan('migrate --force')->assertSuccessful();
}

it('redirects guests from marketing admin routes to login', function (): void {
    bootMarketingPlugin();

    $this->get('/admin/marketing')->assertRedirect('/admin/login');
});

it('allows a super admin to save marketing settings and renders consent-gated output', function (): void {
    bootMarketingPlugin();

    $admin = TpUser::query()->create([
        'name' => 'Marketing Admin',
        'email' => 'marketing-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/marketing')
        ->assertOk()
        ->assertViewIs('tentapress-marketing::index');

    $this->actingAs($admin)
        ->post('/admin/marketing', [
            'consent_enabled' => '1',
            'consent_cookie_name' => 'tp_marketing_test',
            'consent_cookie_max_age_days' => '180',
            'consent_banner_title' => 'Analytics preferences',
            'consent_banner_body' => 'We use analytics to improve the site.',
            'consent_accept_label' => 'Accept analytics',
            'consent_reject_label' => 'Reject analytics',
            'consent_manage_label' => 'Manage preferences',
            'consent_privacy_button_label' => 'Privacy',
            'provider_ga4_enabled' => '1',
            'provider_ga4_measurement_id' => 'G-TEST123456',
            'provider_plausible_script_url' => 'https://plausible.example/js/script.js',
            'custom_script_head' => '<script>window.headMarketing = true;</script>',
            'custom_script_head_analytics_consent' => '1',
            'custom_script_body_close' => '<script>window.bodyCloseMarketing = true;</script>',
        ])
        ->assertRedirect('/admin/marketing')
        ->assertSessionHas('tp_notice_success', 'Marketing settings saved.');

    expect(DB::table('tp_settings')->where('key', 'marketing.providers.ga4.measurement_id')->value('value'))
        ->toBe('G-TEST123456');

    $headWithoutConsent = view('tentapress-marketing::head')->render();
    $consentUi = view('tentapress-marketing::consent')->render();

    expect($headWithoutConsent)->not->toContain('googletagmanager.com/gtag/js');
    expect($headWithoutConsent)->not->toContain('window.headMarketing = true;');
    expect($consentUi)->toContain('Analytics preferences');

    request()->cookies->set('tp_marketing_test', json_encode([
        'analytics' => true,
        'updated_at' => now()->toIso8601String(),
    ], JSON_THROW_ON_ERROR));

    $headWithConsent = view('tentapress-marketing::head')->render();
    $bodyClose = view('tentapress-marketing::body-close')->render();

    expect($headWithConsent)->toContain('googletagmanager.com/gtag/js?id=G-TEST123456');
    expect($headWithConsent)->toContain('window.headMarketing = true;');
    expect($bodyClose)->toContain('window.bodyCloseMarketing = true;');
});
