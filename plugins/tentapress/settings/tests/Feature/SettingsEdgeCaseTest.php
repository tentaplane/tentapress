<?php

declare(strict_types=1);

use TentaPress\Settings\Models\TpSetting;
use TentaPress\Users\Models\TpUser;

it('denies settings access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'regular-settings@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/settings')
        ->assertForbidden();
});

it('validates blog base format when saving settings', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Settings Admin',
        'email' => 'settings-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/settings')
        ->post('/admin/settings', [
            'site_title' => 'Example',
            'tagline' => 'Tagline',
            'home_page_id' => '',
            'blog_base' => 'INVALID BASE!',
        ])
        ->assertRedirect('/admin/settings')
        ->assertSessionHasErrors(['blog_base']);
});

it('renders empty defaults when settings keys do not exist', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Settings Admin',
        'email' => 'settings-defaults@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpSetting::query()->delete();

    $this->actingAs($admin)
        ->get('/admin/settings')
        ->assertOk()
        ->assertViewHas('siteTitle', '')
        ->assertViewHas('tagline', '')
        ->assertViewHas('homePageId', '')
        ->assertViewHas('blogBase', '');
});
