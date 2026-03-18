<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MarketingCapabilitySeeder
{
    public function run(): void
    {
        if (! Schema::hasTable('tp_capabilities') || ! Schema::hasTable('tp_roles') || ! Schema::hasTable('tp_role_capability')) {
            return;
        }

        $now = now();

        DB::table('tp_capabilities')->updateOrInsert(
            ['key' => 'manage_marketing'],
            [
                'label' => 'Manage Marketing',
                'group' => 'Growth',
                'description' => 'Manage analytics providers, consent settings, and marketing scripts.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $roleIds = DB::table('tp_roles')
            ->whereIn('slug', ['administrator', 'editor'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('tp_role_capability')->insertOrIgnore([
                'role_id' => (int) $roleId,
                'capability_key' => 'manage_marketing',
            ]);
        }
    }
}
