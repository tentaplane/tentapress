<?php

declare(strict_types=1);

namespace TentaPress\Users\Http\Admin\Users;

use Illuminate\Validation\ValidationException;
use TentaPress\Users\Models\TpUser;

final class DestroyController
{
    public function __invoke(TpUser $user)
    {
        $authId = (int) (auth()->id() ?? 0);

        if ($authId > 0 && (int) $user->id === $authId) {
            throw ValidationException::withMessages([
                'user' => 'You can’t delete your own account.',
            ]);
        }

        // Detach roles first for clarity (FK cascade may already handle it depending on schema).
        $user->roles()->detach();
        $user->delete();

        return to_route('tp.users.index')
            ->with('tp_notice_success', 'User deleted.');
    }
}
