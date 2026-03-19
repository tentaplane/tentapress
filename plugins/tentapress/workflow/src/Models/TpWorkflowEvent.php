<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TentaPress\Users\Models\TpUser;

final class TpWorkflowEvent extends Model
{
    protected $table = 'tp_workflow_events';

    public $timestamps = false;

    protected $fillable = [
        'workflow_item_id',
        'resource_type',
        'resource_id',
        'event_type',
        'from_state',
        'to_state',
        'actor_user_id',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(TpWorkflowItem::class, 'workflow_item_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'actor_user_id');
    }
}
