<?php

declare(strict_types=1);

namespace TentaPress\Boilerplate\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class BoilerplateCapabilitySeeder
{
    public function run(): void
    {
        if (! Schema::hasTable('tp_capabilities') || ! Schema::hasTable('tp_roles') || ! Schema::hasTable('tp_role_capability')) {
            return;
        }

        DB::table('tp_capabilities')->updateOrInsert(
            ['key' => 'manage_boilerplate'],
            [
                'label' => 'Manage Boilerplate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $roleIds = DB::table('tp_roles')
            ->whereIn('slug', ['administrator'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('tp_role_capability')->updateOrInsert(
                [
                    'role_id' => (int) $roleId,
                    'capability_key' => 'manage_boilerplate',
                ],
                [],
            );
        }
    }
}
