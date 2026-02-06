<?php

declare(strict_types=1);

namespace TentaPress\Media\Models;

use Illuminate\Database\Eloquent\Model;

final class TpMedia extends Model
{
    protected $table = 'tp_media';

    protected $fillable = [
        'title',
        'alt_text',
        'caption',
        'source',
        'source_item_id',
        'source_url',
        'license',
        'license_url',
        'attribution',
        'attribution_html',
        'stock_meta',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'stock_meta' => 'array',
    ];
}
