<?php

declare(strict_types=1);

namespace TentaPress\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpMenuItem extends Model
{
    protected $table = 'tp_menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'target',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    /**
     * @return BelongsTo<TpMenu, TpMenuItem>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(TpMenu::class, 'menu_id');
    }

    /**
     * @return BelongsTo<TpMenuItem, TpMenuItem>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<TpMenuItem, TpMenuItem>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
