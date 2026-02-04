<?php

declare(strict_types=1);

namespace TentaPress\Posts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TentaPress\Users\Models\TpUser;

final class TpPost extends Model
{
    protected $table = 'tp_posts';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'layout',
        'blocks',
        'content',
        'published_at',
        'author_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'content' => 'array',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return (string) $this->status === 'published';
    }

    /**
     * @return Builder<TpPost>
     */
    protected function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
