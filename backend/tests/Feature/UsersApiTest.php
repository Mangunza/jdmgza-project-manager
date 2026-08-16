<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersApiTest extends TestCase
{
    use RefreshDatabase;

    private function createRoleWithPermissions(array $slugs): Role
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role-' . uniqid(),
            'description' => 'Role utilizada nos testes.',
        ]);

        $permissions = collect($slugs)->map(
            fn (string $slug) => Permission::create([
                'name' => $slug,
                'slug' => $slug,
                'description' => 'Permissão de teste.',
            ])
        );

        $role->permissions()->sync($permissions->pluck('id'));

        return $role;
    }

    private function createUserWithPermissions(array $permissions): User
    {
        $role = $this->createRoleWithPermissions($permissions);

        $user = User::factory()->create();

        $user->roles()->attach($role);

        return $user;
    }

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_user_with_view_permission_can_list_users(): void
    {
        $user = $this->createUserWithPermissions([
            'users.view',
        ]);

        User::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_user_without_view_permission_receives_forbidden(): void
    {
        $user = $this->createUserWithPermissions([]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'Não autorizado.',
            ]);
    }

    public function test_user_with_create_permission_can_create_user(): void
    {
        $user = $this->createUserWithPermissions([
            'users.create',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/users', [
            'name' => 'Novo Utilizador',
            'email' => 'novo@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Novo Utilizador')
            ->assertJsonPath('data.email', 'novo@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'novo@example.com',
        ]);

        $createdUser = User::where(
            'email',
            'novo@example.com'
        )->firstOrFail();

        $this->assertTrue(
            Hash::check('Password123!', $createdUser->password)
        );

        $response->assertJsonMissing([
            'password' => 'Password123!',
        ]);
    }

    public function test_user_with_update_permission_can_update_user(): void
    {
        $user = $this->createUserWithPermissions([
            'users.update',
        ]);

        $target = User::factory()->create([
            'name' => 'Nome Antigo',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/users/{$target->id}",
            [
                'name' => 'Nome Atualizado',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Nome Atualizado');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    public function test_user_with_delete_permission_can_delete_user(): void
    {
        $user = $this->createUserWithPermissions([
            'users.delete',
        ]);

        $target = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/users/{$target->id}"
        );

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Utilizador eliminado com sucesso.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $target->id,
        ]);
    }
}
