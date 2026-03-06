<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class TpRedirect extends Model
{
    protected $table = 'tp_redirects';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'is_enabled',
        'origin',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_enabled' => 'bool',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /**
     * @return Builder<TpRedirect>
     */
    protected function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * @return Builder<TpRedirect>
     */
    protected function scopeFromSource(Builder $query, string $sourcePath): Builder
    {
        return $query->where('source_path', $sourcePath);
    }
}
