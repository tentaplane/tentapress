<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpTaxonomy extends Model
{
    protected $table = 'tp_taxonomies';

    protected $fillable = [
        'key',
        'label',
        'singular_label',
        'description',
        'is_hierarchical',
        'is_public',
        'config',
    ];

    protected $casts = [
        'is_hierarchical' => 'bool',
        'is_public' => 'bool',
        'config' => 'array',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(TpTerm::class, 'taxonomy_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TpTermAssignment::class, 'taxonomy_id');
    }

    public function descendantAssignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            TpTermAssignment::class,
            TpTerm::class,
            'taxonomy_id',
            'term_id',
            'id',
            'id'
        );
    }
}
