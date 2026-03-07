<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TpGlobalContentUsage extends Model
{
    protected $table = 'tp_global_content_usages';

    protected $fillable = [
        'global_content_id',
        'owner_type',
        'owner_id',
        'owner_label',
        'editor_driver',
    ];

    public function globalContent(): BelongsTo
    {
        return $this->belongsTo(TpGlobalContent::class, 'global_content_id');
    }
}
