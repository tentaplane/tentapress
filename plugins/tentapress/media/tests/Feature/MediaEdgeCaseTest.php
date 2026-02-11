<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Users\Models\TpUser;

it('denies media admin access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Media Regular User',
        'email' => 'media-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/media')
        ->assertForbidden();
});

it('rejects media uploads with unsupported mime types', function (): void {
    Storage::fake('public');

    $admin = TpUser::query()->create([
        'name' => 'Media Admin',
        'email' => 'media-invalid-upload@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/media/new')
        ->post('/admin/media', [
            'file' => UploadedFile::fake()->create('archive.zip', 10, 'application/zip'),
            'title' => 'Invalid Upload',
        ])
        ->assertRedirect('/admin/media/new')
        ->assertSessionHasErrors(['file']);

    expect(TpMedia::query()->count())->toBe(0);
});

it('rejects media metadata updates that exceed max lengths', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Media Admin',
        'email' => 'media-invalid-update@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $media = TpMedia::query()->create([
        'title' => 'Existing Media',
        'disk' => 'public',
        'path' => 'media/2026/02/existing-file.jpg',
        'original_name' => 'existing-file.jpg',
        'mime_type' => 'image/jpeg',
    ]);

    $this->actingAs($admin)
        ->from('/admin/media/'.$media->id.'/edit')
        ->put('/admin/media/'.$media->id, [
            'title' => str_repeat('a', 256),
            'alt_text' => str_repeat('b', 256),
            'caption' => str_repeat('c', 2001),
        ])
        ->assertRedirect('/admin/media/'.$media->id.'/edit')
        ->assertSessionHasErrors(['title', 'alt_text', 'caption']);
});

it('deletes media records even when original and variant files are missing', function (): void {
    Storage::fake('public');

    $admin = TpUser::query()->create([
        'name' => 'Media Admin',
        'email' => 'media-missing-files@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $media = TpMedia::query()->create([
        'title' => 'Missing Files Asset',
        'disk' => 'public',
        'path' => 'media/2026/02/missing-original.jpg',
        'original_name' => 'missing-original.jpg',
        'mime_type' => 'image/jpeg',
        'variants' => [
            'thumbnail' => ['path' => 'media/2026/02/missing-original-thumb.jpg'],
            'medium' => ['path' => 'media/2026/02/missing-original-medium.jpg'],
        ],
    ]);

    $this->actingAs($admin)
        ->delete('/admin/media/'.$media->id)
        ->assertRedirect('/admin/media')
        ->assertSessionHas('tp_notice_success', 'Media deleted.');

    expect(TpMedia::query()->find($media->id))->toBeNull();
});
