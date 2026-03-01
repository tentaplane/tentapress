<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Models;

use Illuminate\Database\Eloquent\Model;

final class TpRevision extends Model
{
    protected $table = 'tp_revisions';

    protected $fillable = [
        'resource_type',
        'resource_id',
        'title',
        'slug',
        'status',
        'layout',
        'editor_driver',
        'blocks',
        'content',
        'author_id',
        'published_at',
        'created_by',
        'snapshot_hash',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'content' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
