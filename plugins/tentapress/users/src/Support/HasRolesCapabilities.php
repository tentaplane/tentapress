<?php

declare(strict_types=1);

namespace TentaPress\Users\Support;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use TentaPress\Users\Models\TpRole;

trait HasRolesCapabilities
{
    private ?array $tpCapabilityCache = null;

    /**
     * @return BelongsToMany<TpRole>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            TpRole::class,
            'tp_user_roles',
            'user_id',
            'role_id'
        );
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin ?? false);
    }

    public function hasCapability(string $capability): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $capability = trim($capability);
        if ($capability === '') {
            return false;
        }

        $caps = $this->resolvedCapabilities();

        return isset($caps[$capability]) && $caps[$capability] === true;
    }

    /**
     * @return array<string,bool>
     */
    private function resolvedCapabilities(): array
    {
        if ($this->tpCapabilityCache !== null) {
            return $this->tpCapabilityCache;
        }

        $userId = (int) ($this->id ?? 0);
        if ($userId <= 0) {
            return $this->tpCapabilityCache = [];
        }

        $keys = DB::table('tp_user_roles')
            ->join('tp_role_capability', 'tp_user_roles.role_id', '=', 'tp_role_capability.role_id')
            ->where('tp_user_roles.user_id', $userId)
            ->pluck('tp_role_capability.capability_key')
            ->all();

        $out = [];
        foreach ($keys as $k) {
            if (is_string($k) && $k !== '') {
                $out[$k] = true;
            }
        }

        return $this->tpCapabilityCache = $out;
    }
}
