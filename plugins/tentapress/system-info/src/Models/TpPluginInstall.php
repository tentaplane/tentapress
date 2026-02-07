<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Models;

use Illuminate\Database\Eloquent\Model;

final class TpPluginInstall extends Model
{
    protected $table = 'tp_plugin_installs';

    protected $fillable = [
        'package',
        'status',
        'requested_by',
        'started_at',
        'finished_at',
        'output',
        'error',
    ];

    protected $casts = [
        'requested_by' => 'int',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
