<?php

declare(strict_types=1);

namespace TentaPress\Users\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use TentaPress\Users\Models\TpUser;

final class MakeAdminUserCommand extends Command
{
    protected $signature = 'tp:users:make-admin
        {email : Email address}
        {--name=Admin : Display name}
        {--password= : Password (if omitted, will generate)}
        {--super : Make user a super admin}
    ';

    protected $description = 'Create an admin user for TentaPress';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) $this->option('name');
        $password = (string) ($this->option('password') ?? '');

        if ($password === '') {
            $password = Str::random(20);
        }

        $existing = TpUser::query()->where('email', $email)->first();
        if ($existing) {
            $this->error("User already exists: {$email}");

            return self::FAILURE;
        }

        $user = TpUser::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_super_admin' => (bool) $this->option('super'),
        ]);

        // Ensure Administrator role exists and is attached (unless super admin)
        if (! (bool) $this->option('super')) {
            $roleId = 'administrator';

            $role = DB::table('tp_roles')->where('id', $roleId)->first();
            if (! $role) {
                DB::table('tp_roles')->insert([
                    'id' => $roleId,
                    'name' => 'Administrator',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Attach role
            DB::table('tp_user_roles')->insert([
                'user_id' => (int) $user->getKey(),
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Give role default caps
            $caps = [
                'view_admin',
                'manage_users',
                'manage_plugins',
                'manage_themes',
                'manage_pages',
                'manage_blocks',
            ];

            foreach ($caps as $cap) {
                DB::table('tp_capabilities')->updateOrInsert(
                    ['key' => $cap],
                    ['label' => Str::of($cap)->replace('_', ' ')->title()->toString(), 'updated_at' => now(), 'created_at' => now()]
                );

                DB::table('tp_role_capabilities')->updateOrInsert(
                    ['role_id' => $roleId, 'capability_key' => $cap],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $this->info("Created user: {$email}");
        $this->line("Password: {$password}");

        return self::SUCCESS;
    }
}
