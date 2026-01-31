<?php

declare(strict_types=1);

namespace TentaPress\Users\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use TentaPress\Users\Support\HasRolesCapabilities;

final class TpUser extends Authenticatable
{
    use HasRolesCapabilities;
    use Notifiable;

    protected $table = 'tp_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super_admin' => 'bool',
    ];
}
