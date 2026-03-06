<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Models;

use Illuminate\Database\Eloquent\Model;

final class TpRedirectEvent extends Model
{
    protected $table = 'tp_redirect_events';

    protected $fillable = [
        'redirect_id',
        'action',
        'source_path',
        'target_path',
        'actor_user_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'redirect_id' => 'integer',
            'actor_user_id' => 'integer',
            'meta' => 'array',
        ];
    }
}
