<?php

declare(strict_types=1);

namespace TentaPress\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class TpRole extends Model
{
    protected $table = 'tp_roles';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return BelongsToMany<TpCapability>
     */
    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            TpCapability::class,
            'tp_role_capability',
            'role_id',
            'capability_key',
            'id',
            'key'
        );
    }
}
