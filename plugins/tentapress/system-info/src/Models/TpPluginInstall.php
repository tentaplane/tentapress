<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Models;

use Illuminate\Database\Eloquent\Model;

final class TpPluginInstall extends Model
{
    public const UPDATE_PLUGINS_SENTINEL = '__tp_update_plugins__';

    public const UPDATE_FULL_SENTINEL = '__tp_update_full__';

    protected $table = 'tp_plugin_installs';

    protected $fillable = [
        'package',
        'status',
        'requested_by',
        'started_at',
        'finished_at',
        'output',
        'error',
    ];

    protected $casts = [
        'requested_by' => 'int',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function isUpdateAttempt(): bool
    {
        return in_array((string) $this->package, [self::UPDATE_PLUGINS_SENTINEL, self::UPDATE_FULL_SENTINEL], true);
    }

    public function displayPackage(): string
    {
        if ((string) $this->package === self::UPDATE_FULL_SENTINEL) {
            return 'composer update (full project)';
        }

        if ((string) $this->package === self::UPDATE_PLUGINS_SENTINEL) {
            return 'all installed plugins';
        }

        return (string) $this->package;
    }

    public function manualCommand(): string
    {
        if ((string) $this->package === self::UPDATE_FULL_SENTINEL) {
            return 'composer update --with-all-dependencies --no-interaction --no-progress';
        }

        if ((string) $this->package === self::UPDATE_PLUGINS_SENTINEL) {
            return 'composer update tentapress/* --with-all-dependencies --no-interaction --no-progress';
        }

        return 'composer require ' . (string) $this->package;
    }
}
