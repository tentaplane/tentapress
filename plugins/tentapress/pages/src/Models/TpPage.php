<?php

declare(strict_types=1);

namespace TentaPress\Pages\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class TpPage extends Model
{
    protected $table = 'tp_pages';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'layout',
        'blocks',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'published_at' => 'datetime',
    ];

    public function isPublished(): bool
    {
        return (string) $this->status === 'published';
    }

    /**
     * @return Builder<TpPage>
     */
    protected function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
