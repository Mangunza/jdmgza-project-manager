<?php

namespace App\Domains\Auth\Services;

use App\Models\User;

class AuthorizationService
{
    /**
     * Verifica se o utilizador possui uma determinada permissão.
     */
    public function userHasPermission(
        User $user,
        string $permission
    ): bool {
        return $user
            ->roles()
            ->whereHas(
                'permissions',
                fn ($query) => $query->where('slug', $permission)
            )
            ->exists();
    }

    /**
     * Verifica se o utilizador possui determinada role.
     */
    public function userHasRole(
        User $user,
        string $role
    ): bool {
        return $user
            ->roles()
            ->where('slug', $role)
            ->exists();
    }
}
