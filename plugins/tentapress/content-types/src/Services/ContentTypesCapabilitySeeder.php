<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ContentTypesCapabilitySeeder
{
    public function run(): void
    {
        if (! Schema::hasTable('tp_capabilities') || ! Schema::hasTable('tp_roles') || ! Schema::hasTable('tp_role_capability')) {
            return;
        }

        $capabilities = [
            'manage_content_types' => [
                'label' => 'Manage Content Types',
                'description' => 'Create and manage content type definitions.',
            ],
            'manage_content_entries' => [
                'label' => 'Manage Content Entries',
                'description' => 'Create and edit content entries.',
            ],
            'publish_content_entries' => [
                'label' => 'Publish Content Entries',
                'description' => 'Publish and unpublish content entries.',
            ],
        ];

        $now = now();

        foreach ($capabilities as $key => $definition) {
            DB::table('tp_capabilities')->updateOrInsert(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'group' => 'Content',
                    'description' => $definition['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $roleIds = DB::table('tp_roles')
            ->whereIn('slug', ['administrator', 'editor'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('tp_role_capability')->insertOrIgnore([
                'role_id' => (int) $roleId,
                'capability_key' => 'manage_content_types',
            ]);

            DB::table('tp_role_capability')->insertOrIgnore([
                'role_id' => (int) $roleId,
                'capability_key' => 'manage_content_entries',
            ]);

            DB::table('tp_role_capability')->insertOrIgnore([
                'role_id' => (int) $roleId,
                'capability_key' => 'publish_content_entries',
            ]);
        }
    }
}
