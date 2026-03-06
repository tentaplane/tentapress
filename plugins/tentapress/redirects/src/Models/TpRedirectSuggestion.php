<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class TpRedirectSuggestion extends Model
{
    protected $table = 'tp_redirect_suggestions';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'origin',
        'state',
        'conflict_type',
        'decision_by',
        'decision_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'decision_by' => 'integer',
            'decision_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * @return Builder<TpRedirectSuggestion>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('state', 'pending');
    }
}
