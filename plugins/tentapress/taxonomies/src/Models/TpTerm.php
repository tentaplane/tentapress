<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpTerm extends Model
{
    protected $table = 'tp_terms';

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(TpTaxonomy::class, 'taxonomy_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TpTermAssignment::class, 'term_id');
    }
}
