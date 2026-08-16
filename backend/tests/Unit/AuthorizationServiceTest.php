<?php

namespace Tests\Unit;

use App\Domains\Auth\Services\AuthorizationService;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthorizationService $authorizationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationService = app(
            AuthorizationService::class
        );
    }

    public function test_user_with_role_permission_is_authorized(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        $permission = Permission::create([
            'name' => 'Criar utilizadores',
            'slug' => 'users.create',
            'description' => 'Permite criar utilizadores.',
        ]);

        $user->roles()->attach($role);
        $role->permissions()->attach($permission);

        $this->assertTrue(
            $this->authorizationService->userHasPermission(
                $user,
                'users.create'
            )
        );
    }

    public function test_user_without_permission_is_not_authorized(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'Membro',
            'slug' => 'member',
            'description' => 'Utilizador comum.',
        ]);

        $user->roles()->attach($role);

        $this->assertFalse(
            $this->authorizationService->userHasPermission(
                $user,
                'users.create'
            )
        );
    }

    public function test_user_has_role(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        $user->roles()->attach($role);

        $this->assertTrue(
            $this->authorizationService->userHasRole(
                $user,
                'admin'
            )
        );
    }

    public function test_user_does_not_have_role(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(
            $this->authorizationService->userHasRole(
                $user,
                'admin'
            )
        );
    }
}
