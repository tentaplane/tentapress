<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class TpTermAssignment extends Model
{
    protected $table = 'tp_term_assignments';

    protected $fillable = [
        'taxonomy_id',
        'term_id',
        'assignable_type',
        'assignable_id',
    ];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(TpTaxonomy::class, 'taxonomy_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(TpTerm::class, 'term_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
