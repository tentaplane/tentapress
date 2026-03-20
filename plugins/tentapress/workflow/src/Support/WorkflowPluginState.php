<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class WorkflowPluginState
{
    public static function isEnabled(): bool
    {
        if (! Schema::hasTable('tp_plugins')) {
            return false;
        }

        $enabled = DB::table('tp_plugins')
            ->where('id', 'tentapress/workflow')
            ->value('enabled');

        return (int) $enabled === 1;
    }
}
