<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TpContentTypeField extends Model
{
    protected $table = 'tp_content_type_fields';

    protected $fillable = [
        'content_type_id',
        'key',
        'label',
        'field_type',
        'sort_order',
        'required',
        'config',
    ];

    protected $casts = [
        'required' => 'boolean',
        'config' => 'array',
    ];

    /**
     * @return BelongsTo<TpContentType, $this>
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(TpContentType::class, 'content_type_id');
    }
}
