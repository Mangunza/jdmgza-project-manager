<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnsurePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson(
            '/api/authorization-test/users-create'
        );

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_receives_forbidden(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/authorization-test/users-create'
        );

        $response->assertForbidden();
    }

    public function test_user_with_permission_can_access_route(): void
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

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/authorization-test/users-create'
        );

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Permissão users.create validada com sucesso.',
            ]);
    }
}
