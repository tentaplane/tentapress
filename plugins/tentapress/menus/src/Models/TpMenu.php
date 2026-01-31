<?php

declare(strict_types=1);

namespace TentaPress\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TpMenu extends Model
{
    protected $table = 'tp_menus';

    protected $fillable = [
        'name',
        'slug',
        'created_by',
        'updated_by',
    ];

    /**
     * @return HasMany<TpMenuItem, TpMenu>
     */
    public function items(): HasMany
    {
        return $this->hasMany(TpMenuItem::class, 'menu_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
