<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use TentaPress\Users\Models\TpUser;

final class StoreController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('tp_users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'is_super_admin' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['integer'],
        ]);

        $user = TpUser::query()->create([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => Hash::make((string) $data['password']),
            'is_super_admin' => (bool) ($data['is_super_admin'] ?? false),
        ]);

        $roleIds = array_values(array_filter(array_map(intval(...), $data['roles'] ?? [])));
        $user->roles()->sync($roleIds);

        return to_route('tp.users.edit', ['user' => $user->id])
            ->with('tp_notice_success', 'User created.');
    }
}
