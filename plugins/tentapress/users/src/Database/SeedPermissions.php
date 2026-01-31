<?php

declare(strict_types=1);

namespace TentaPress\Users\Database;

use Illuminate\Support\Facades\DB;

final class SeedPermissions
{
    /**
     * Seed first-party capabilities + roles.
     */
    public function run(): void
    {
        $now = now();

        $capabilities = [
            // System / access
            ['key' => 'manage_users', 'label' => 'Manage Users', 'group' => 'System', 'description' => 'Create and manage admin users.'],
            ['key' => 'manage_roles', 'label' => 'Manage Roles', 'group' => 'System', 'description' => 'Create roles and assign capabilities.'],
            ['key' => 'view_system_info', 'label' => 'View System Info', 'group' => 'System', 'description' => 'View system diagnostics.'],

            // Content
            ['key' => 'manage_pages', 'label' => 'Manage Pages', 'group' => 'Content', 'description' => 'Create and edit pages.'],
            ['key' => 'manage_posts', 'label' => 'Manage Posts', 'group' => 'Content', 'description' => 'Create and edit posts.'],
            ['key' => 'manage_media', 'label' => 'Manage Media', 'group' => 'Content', 'description' => 'Upload and manage media files.'],
            ['key' => 'manage_blocks', 'label' => 'Manage Blocks', 'group' => 'Content', 'description' => 'Access blocks registry/admin screens.'],

            // Appearance
            ['key' => 'manage_themes', 'label' => 'Manage Themes', 'group' => 'Appearance', 'description' => 'Activate and manage themes.'],
            ['key' => 'manage_menus', 'label' => 'Manage Menus', 'group' => 'Appearance', 'description' => 'Create and assign navigation menus.'],
        ];

        DB::table('tp_capabilities')->upsert(
            array_map(fn ($c) => $c + ['created_at' => $now, 'updated_at' => $now], $capabilities),
            ['key'],
            ['label', 'group', 'description', 'updated_at']
        );

        // Roles
        $roles = [
            ['slug' => 'administrator', 'name' => 'Administrator'],
            ['slug' => 'editor', 'name' => 'Editor'],
            ['slug' => 'viewer', 'name' => 'Viewer'],
        ];

        DB::table('tp_roles')->upsert(
            array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $roles),
            ['slug'],
            ['name', 'updated_at']
        );

        $roleIdsBySlug = DB::table('tp_roles')->pluck('id', 'slug')->all();

        // Role → capabilities
        $adminCaps = array_column($capabilities, 'key');
        $editorCaps = ['manage_pages', 'manage_posts', 'manage_media', 'manage_blocks', 'manage_menus'];
        $viewerCaps = ['view_system_info'];

        $this->syncRoleCaps((int) ($roleIdsBySlug['administrator'] ?? 0), $adminCaps);
        $this->syncRoleCaps((int) ($roleIdsBySlug['editor'] ?? 0), $editorCaps);
        $this->syncRoleCaps((int) ($roleIdsBySlug['viewer'] ?? 0), $viewerCaps);
    }

    /**
     * @param  array<int,string>  $caps
     */
    private function syncRoleCaps(int $roleId, array $caps): void
    {
        if ($roleId <= 0) {
            return;
        }

        DB::table('tp_role_capability')->where('role_id', $roleId)->delete();

        $rows = [];
        foreach ($caps as $cap) {
            $cap = trim((string) $cap);
            if ($cap === '') {
                continue;
            }
            $rows[] = [
                'role_id' => $roleId,
                'capability_key' => $cap,
            ];
        }

        if ($rows !== []) {
            DB::table('tp_role_capability')->insert($rows);
        }
    }
}
