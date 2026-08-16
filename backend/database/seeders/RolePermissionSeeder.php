<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = $this->createPermissions();

        $this->createRoles($permissions);
    }

    /**
     * Cria ou atualiza as permissões do sistema.
     *
     * @return array<string, Permission>
     */
    private function createPermissions(): array
    {
        $definitions = [
            [
                'name' => 'Visualizar dashboard',
                'slug' => 'dashboard.view',
                'description' => 'Permite visualizar o dashboard.',
            ],

            [
                'name' => 'Visualizar utilizadores',
                'slug' => 'users.view',
                'description' => 'Permite visualizar utilizadores.',
            ],
            [
                'name' => 'Criar utilizadores',
                'slug' => 'users.create',
                'description' => 'Permite criar utilizadores.',
            ],
            [
                'name' => 'Atualizar utilizadores',
                'slug' => 'users.update',
                'description' => 'Permite atualizar utilizadores.',
            ],
            [
                'name' => 'Eliminar utilizadores',
                'slug' => 'users.delete',
                'description' => 'Permite eliminar utilizadores.',
            ],

            [
                'name' => 'Visualizar roles',
                'slug' => 'roles.view',
                'description' => 'Permite visualizar roles.',
            ],
            [
                'name' => 'Criar roles',
                'slug' => 'roles.create',
                'description' => 'Permite criar roles.',
            ],
            [
                'name' => 'Atualizar roles',
                'slug' => 'roles.update',
                'description' => 'Permite atualizar roles.',
            ],
            [
                'name' => 'Eliminar roles',
                'slug' => 'roles.delete',
                'description' => 'Permite eliminar roles.',
            ],

            [
                'name' => 'Visualizar permissões',
                'slug' => 'permissions.view',
                'description' => 'Permite visualizar permissões.',
            ],
            [
                'name' => 'Criar permissões',
                'slug' => 'permissions.create',
                'description' => 'Permite criar permissões.',
            ],
            [
                'name' => 'Atualizar permissões',
                'slug' => 'permissions.update',
                'description' => 'Permite atualizar permissões.',
            ],
            [
                'name' => 'Eliminar permissões',
                'slug' => 'permissions.delete',
                'description' => 'Permite eliminar permissões.',
            ],

            [
                'name' => 'Visualizar projetos',
                'slug' => 'projects.view',
                'description' => 'Permite visualizar projetos.',
            ],
            [
                'name' => 'Criar projetos',
                'slug' => 'projects.create',
                'description' => 'Permite criar projetos.',
            ],
            [
                'name' => 'Atualizar projetos',
                'slug' => 'projects.update',
                'description' => 'Permite atualizar projetos.',
            ],
            [
                'name' => 'Eliminar projetos',
                'slug' => 'projects.delete',
                'description' => 'Permite eliminar projetos.',
            ],
        ];

        $permissions = [];

        foreach ($definitions as $definition) {
            $permission = Permission::updateOrCreate(
                ['slug' => $definition['slug']],
                $definition,
            );

            $permissions[$permission->slug] = $permission;
        }

        return $permissions;
    }

    /**
     * Cria os roles e associa as permissões correspondentes.
     *
     * @param array<string, Permission> $permissions
     */
    private function createRoles(array $permissions): void
    {
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrador',
                'description' => 'Acesso total ao sistema.',
            ],
        );

        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Gestor',
                'description' => 'Gestão de projetos e utilizadores.',
            ],
        );

        $member = Role::updateOrCreate(
            ['slug' => 'member'],
            [
                'name' => 'Membro',
                'description' => 'Utilizador comum do sistema.',
            ],
        );

        $admin->permissions()->sync(
            array_values($permissions),
        );

        $manager->permissions()->sync(
            $this->permissionsFor(
                $permissions,
                [
                    'dashboard.view',

                    'users.view',
                    'users.update',

                    'projects.view',
                    'projects.create',
                    'projects.update',
                    'projects.delete',
                ],
            ),
        );

        $member->permissions()->sync(
            $this->permissionsFor(
                $permissions,
                [
                    'dashboard.view',
                    'projects.view',
                    'projects.update',
                ],
            ),
        );
    }

    /**
     * Obtém permissões através dos seus slugs.
     *
     * @param array<string, Permission> $permissions
     * @param array<string> $slugs
     * @return array<int, Permission>
     */
    private function permissionsFor(
        array $permissions,
        array $slugs,
    ): array {
        return array_values(
            array_intersect_key(
                $permissions,
                array_flip($slugs),
            ),
        );
    }
}
