<?php

namespace App\Domains\Users\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use LogicException;

class UserService
{
    public function list(): LengthAwarePaginator
    {
        return User::query()
            ->with('roles.permissions')
            ->latest()
            ->paginate(15);
    }

    public function create(array $data, User $actor): User
    {
        $hasExplicitRoles = array_key_exists('role_slugs', $data);
        $roleSlugs = $data['role_slugs'] ?? ['member'];

        unset($data['role_slugs']);

        if ($hasExplicitRoles) {
            $this->assertCanAssignRoles($actor);
        }

        return DB::transaction(function () use (
            $data,
            $roleSlugs,
            $actor,

        ): User {
            if (in_array('admin', $roleSlugs, true)) {
                $this->lockAdminRole();

                if (! $actor->hasRole('admin')) {
                    throw new AuthorizationException(
                        'Só um administrador pode atribuir o papel admin.'
                    );
                }
            }

            $roles = Role::query()
                ->whereIn('slug', $roleSlugs)
                ->get();

            if ($roles->count() !== count($roleSlugs)) {
                throw new LogicException(
                    'Os roles obrigatórios não foram inicializados.'
                );
            }

            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $user->roles()->sync($roles->modelKeys());

            return $user->fresh('roles.permissions');
        });
    }

    public function update(
        User $user,
        array $data,
        User $actor
    ): User {
        $hasRoleChanges = array_key_exists('role_slugs', $data);
        $roleSlugs = $data['role_slugs'] ?? [];

        if ($hasRoleChanges) {
            $this->assertCanAssignRoles($actor);
        }

        $adminRole = null;

        if ($hasRoleChanges) {
            $adminRole = $this->lockAdminRole();

            if (
                in_array('admin', $roleSlugs, true)
                && $adminRole === null
            ) {
                throw new LogicException(
                    'O role admin não foi inicializado.'
                );
            }
        }

        unset($data['role_slugs']);

        return DB::transaction(function () use (
            $user,
            $data,
            $actor,
            $hasRoleChanges,
            $roleSlugs,
            $adminRole
        ): User {
            // Todas as alterações de papéis adquirem primeiro
            // o mesmo bloqueio, evitando verificações concorrentes.
            if ($hasRoleChanges) {
                $this->lockAdminRole();
            }

            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $lockedUser->update($data);

            if ($hasRoleChanges) {
                $roles = Role::query()
                    ->whereIn('slug', $roleSlugs)
                    ->get();

                if ($roles->count() !== count($roleSlugs)) {
                    throw new LogicException(
                        'Os roles indicados não foram encontrados.'
                    );
                }

                $hasAdminRole = $adminRole !== null
                    && $lockedUser->roles()
                    ->whereKey($adminRole->getKey())
                    ->exists();
                $assigningAdmin = in_array('admin', $roleSlugs, true);
                $removingAdmin = $hasAdminRole && ! $assigningAdmin;

                if (
                    $assigningAdmin
                    && ! $actor->hasRole('admin')
                ) {
                    throw new AuthorizationException(
                        'Só um administrador pode atribuir o papel admin.'
                    );
                }

                if (
                    $removingAdmin
                    && ! $actor->hasRole('admin')
                ) {
                    throw new AuthorizationException(
                        'Só um administrador pode retirar o papel admin.'
                    );
                }

                if ($removingAdmin) {
                    $this->assertNotLastAdministrator();
                }

                $lockedUser->roles()->sync($roles->modelKeys());
            }

            return $lockedUser->fresh('roles.permissions');
        });
    }

    public function delete(User $user, User $actor): void
    {
        if (! $actor->hasPermission('users.delete')) {
            throw new AuthorizationException(
                'Não possui permissão para eliminar usuários.'
            );
        }
        DB::transaction(function () use ($user): void {
            // A mesma ordem de bloqueio é usada em update().
            $adminRole = $this->lockAdminRole();

            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $adminRole !== null
                && $lockedUser->roles()
                ->whereKey($adminRole->getKey())
                ->exists()
            ) {
                $this->assertNotLastAdministrator();
            }

            $lockedUser->delete();
        });
    }

    private function assertCanAssignRoles(User $actor): void
    {
        if (! $actor->hasPermission('roles.update')) {
            throw new AuthorizationException(
                'Não possui permissão para atribuir roles.'
            );
        }
    }

    private function lockAdminRole(): ?Role
    {
        $role = Role::query()
            ->where('slug', 'admin')
            ->lockForUpdate()
            ->first();

        return $role;
    }

    private function assertNotLastAdministrator(): void
    {
        $administratorCount = User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('slug', 'admin');
            })
            ->count();

        if ($administratorCount <= 1) {
            throw ValidationException::withMessages([
                'user' => [
                    'Não é possível remover ou eliminar o último administrador.',
                ],
            ]);
        }
    }
}
