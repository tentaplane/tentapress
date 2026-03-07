<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpGlobalContent extends Model
{
    protected $table = 'tp_global_contents';

    protected $fillable = [
        'title',
        'slug',
        'kind',
        'status',
        'editor_driver',
        'blocks',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(TpGlobalContentUsage::class, 'global_content_id');
    }

    public function isPublished(): bool
    {
        return (string) $this->status === 'published';
    }

    /**
     * @return Builder<TpGlobalContent>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
