<?php

declare(strict_types=1);

namespace TentaPress\Users;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use TentaPress\Users\Console\MakeAdminUserCommand;
use TentaPress\Users\Console\PermissionsCommand;
use TentaPress\Users\Database\SeedPermissions;
use TentaPress\Users\Models\TpUser;

final class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Make TpUser the default auth model whenever this plugin is enabled.
        // This avoids Laravel looking for App\Models\User.
        config()->set('auth.providers.users.model', TpUser::class);

        // Ensure the default password broker points at the same provider.
        // (Harmless even if you don't use password resets yet.)
        config()->set('auth.passwords.users.provider', 'users');

        $this->app->singleton(SeedPermissions::class);
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if (is_object($user) && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            if (is_object($user) && method_exists($user, 'hasCapability') && $user->hasCapability($ability)) {
                return true;
            }

            return null;
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-users');

        $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAdminUserCommand::class,
                PermissionsCommand::class,
            ]);
        }
    }
}
