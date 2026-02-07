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
        'editor_driver',
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

    /**
     * @return array<string,mixed>|null
     */
    protected function getContentAttribute(mixed $value): ?array
    {
        $raw = $this->attributes['content'] ?? $value;

        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function getEditorDriverAttribute(mixed $value): string
    {
        $raw = $this->attributes['editor_driver'] ?? $value;

        return is_string($raw) && $raw !== '' ? $raw : 'blocks';
    }

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
