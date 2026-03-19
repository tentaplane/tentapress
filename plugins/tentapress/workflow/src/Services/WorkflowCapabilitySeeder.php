<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class WorkflowCapabilitySeeder
{
    public function run(): void
    {
        if (! Schema::hasTable('tp_capabilities') || ! Schema::hasTable('tp_roles') || ! Schema::hasTable('tp_role_capability')) {
            return;
        }

        $capabilities = [
            'view_workflow_queue' => 'View Workflow Queue',
            'review_content' => 'Review Content',
            'approve_content' => 'Approve Content',
            'publish_content' => 'Publish Content',
        ];

        foreach ($capabilities as $key => $label) {
            DB::table('tp_capabilities')->updateOrInsert(
                ['key' => $key],
                [
                    'label' => $label,
                    'group' => 'Workflow',
                    'description' => $label.'.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $roleIds = DB::table('tp_roles')
            ->whereIn('slug', ['administrator'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach (array_keys($capabilities) as $capability) {
                DB::table('tp_role_capability')->updateOrInsert(
                    [
                        'role_id' => (int) $roleId,
                        'capability_key' => $capability,
                    ],
                    [],
                );
            }
        }
    }
}
