<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpContentType extends Model
{
    protected $table = 'tp_content_types';

    protected $fillable = [
        'key',
        'singular_label',
        'plural_label',
        'description',
        'base_path',
        'default_layout',
        'default_editor_driver',
        'archive_enabled',
        'api_visibility',
    ];

    protected $casts = [
        'archive_enabled' => 'boolean',
    ];

    /**
     * @return HasMany<TpContentTypeField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(TpContentTypeField::class, 'content_type_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<TpContentEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TpContentEntry::class, 'content_type_id');
    }
}
