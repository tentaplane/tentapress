<?php

declare(strict_types=1);

namespace TentaPress\Users\Models;

use Illuminate\Database\Eloquent\Model;

final class TpCapability extends Model
{
    protected $table = 'tp_capabilities';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'label',
        'group',
        'description',
    ];
}
