<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use TentaPress\Users\Models\TpUser;

final class UpdateController
{
    public function __invoke(Request $request, TpUser $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('tp_users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_super_admin' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['integer'],
        ]);

        $isSuperAdmin = (bool) ($data['is_super_admin'] ?? false);
        $roleIds = array_values(array_filter(array_map(intval(...), $data['roles'] ?? [])));

        // Prevent locking yourself out:
        // If editing yourself, you must remain Super Admin OR keep at least one role.
        $authId = (int) (auth()->id() ?? 0);
        if ($authId > 0 && (int) $user->id === $authId) {
            if ($isSuperAdmin === false && count($roleIds) === 0) {
                throw ValidationException::withMessages([
                    'roles' => 'You can’t remove your own access. Keep Super Admin enabled or assign at least one role.',
                ]);
            }
        }

        $user->name = (string) $data['name'];
        $user->email = (string) $data['email'];
        $user->is_super_admin = $isSuperAdmin;

        $password = trim((string) ($data['password'] ?? ''));

        if ($password !== '') {
            $user->password = Hash::make($password);
        }

        $user->save();

        $user->roles()->sync($roleIds);

        return to_route('tp.users.edit', ['user' => $user->id])
            ->with('tp_notice_success', 'User updated.');
    }
}
