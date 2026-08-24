<?php

namespace App\Domains\Users\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;

class UserService
{
    public function list(): LengthAwarePaginator
    {
        return User::query()
            ->latest()
            ->paginate(15);
    }

    public function create(array $data): User
    {
        $roleSlugs = $data['role_slugs'] ?? ['member'];
        unset($data['role_slugs']);

        return DB::transaction(function () use ($data, $roleSlugs): User {
            $roles = Role::query()
                ->whereIn('slug', $roleSlugs)
                ->get();

            if ($roles->count() !== count($roleSlugs)) {
                throw new LogicException(
                    'Os roles obrigatórios não foram inicializados.',
                );
            }

            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $user->roles()->sync($roles->modelKeys());

            return $user->fresh('roles');
        });
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
