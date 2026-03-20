<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TpContentEntry extends Model
{
    protected $table = 'tp_content_entries';

    protected $fillable = [
        'content_type_id',
        'title',
        'slug',
        'status',
        'layout',
        'editor_driver',
        'blocks',
        'content',
        'field_values',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'blocks' => 'array',
        'content' => 'array',
        'field_values' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<TpContentType, $this>
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(TpContentType::class, 'content_type_id');
    }

    public function isPublished(): bool
    {
        if ((string) $this->status !== 'published') {
            return false;
        }

        if ($this->published_at === null) {
            return true;
        }

        return $this->published_at->lte(now());
    }

    /**
     * @return Builder<TpContentEntry>
     */
    protected function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $nested): void {
                $nested
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function permalink(): string
    {
        $basePath = trim((string) ($this->contentType?->base_path ?? ''), '/');

        return '/'.$basePath.'/'.$this->slug;
    }
}
