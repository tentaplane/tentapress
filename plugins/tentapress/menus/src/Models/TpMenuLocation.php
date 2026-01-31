<?php

declare(strict_types=1);

namespace TentaPress\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TpMenuLocation extends Model
{
    protected $table = 'tp_menu_locations';

    protected $fillable = [
        'location_key',
        'menu_id',
    ];

    /**
     * @return BelongsTo<TpMenu, TpMenuLocation>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(TpMenu::class, 'menu_id');
    }
}
