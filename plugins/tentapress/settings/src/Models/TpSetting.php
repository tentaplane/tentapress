<?php

declare(strict_types=1);

namespace TentaPress\Settings\Models;

use Illuminate\Database\Eloquent\Model;

final class TpSetting extends Model
{
    protected $table = 'tp_settings';

    protected $fillable = [
        'key',
        'value',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'bool',
    ];
}
