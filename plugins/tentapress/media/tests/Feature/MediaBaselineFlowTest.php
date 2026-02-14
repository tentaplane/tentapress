<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Users\Models\TpUser;

it('redirects guests from media admin routes to login', function (): void {
    $this->get('/admin/media')->assertRedirect('/admin/login');
    $this->post('/admin/media')->assertRedirect('/admin/login');
});

it('allows a super admin to view media index and upload a media file', function (): void {
    Storage::fake('public');

    $admin = TpUser::query()->create([
        'name' => 'Media Admin',
        'email' => 'media-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/media')
        ->assertOk()
        ->assertViewIs('tentapress-media::media.index');

    $this->actingAs($admin)
        ->post('/admin/media', [
            'file' => UploadedFile::fake()->create('track.mp3', 200, 'audio/mpeg'),
            'title' => 'Launch Track',
            'alt_text' => 'Audio asset',
            'caption' => 'Campaign launch track',
        ])
        ->assertRedirect('/admin/media/1/edit')
        ->assertSessionHas('tp_notice_success', 'Media uploaded.');

    $media = TpMedia::query()->find(1);

    expect($media)->not->toBeNull();
    expect($media?->title)->toBe('Launch Track');
    expect($media?->mime_type)->toContain('audio');

    Storage::disk('public')->assertExists((string) $media?->path);
});

it('renders pagination controls in grid view', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Media Grid Admin',
        'email' => 'media-grid-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    foreach (range(1, 25) as $index) {
        TpMedia::query()->create([
            'title' => 'Grid Item '.$index,
            'disk' => 'public',
            'path' => 'media/2026/02/grid-item-'.$index.'.jpg',
            'original_name' => 'grid-item-'.$index.'.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    $this->actingAs($admin)
        ->get('/admin/media?view=grid')
        ->assertOk()
        ->assertSee('page=2', false);
});
