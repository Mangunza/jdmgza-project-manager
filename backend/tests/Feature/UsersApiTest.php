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
            'slug' => 'test-role-'.uniqid(),
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
        Role::create([
            'name' => 'Membro',
            'slug' => 'member',
            'description' => 'Utilizador comum.',
        ]);

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
            ->assertJsonPath('data.email', 'novo@example.com')
            ->assertJsonPath('data.roles.0', 'member');

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

        $this->assertTrue($createdUser->hasRole('member'));
    }

    public function test_user_without_role_management_permission_cannot_assign_roles(): void
    {
        Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        $user = $this->createUserWithPermissions(['users.create']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/users', [
            'name' => 'Administrador Indevido',
            'email' => 'admin-indevido@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_slugs' => ['admin'],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin-indevido@example.com',
        ]);
    }

    public function test_user_with_role_management_permission_can_assign_roles(): void
    {
        Role::create([
            'name' => 'Membro',
            'slug' => 'member',
            'description' => 'Utilizador comum.',
        ]);
        Role::create([
            'name' => 'Gestor',
            'slug' => 'manager',
            'description' => 'Gestão de projetos.',
        ]);

        $user = $this->createUserWithPermissions([
            'users.create',
            'roles.update',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/users', [
            'name' => 'Novo Gestor',
            'email' => 'gestor@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_slugs' => ['manager'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.roles.0', 'manager');
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

    public function test_user_without_admin_role_cannot_assign_admin_on_update(): void
    {
        Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        $actorRole = $this->createRoleWithPermissions([
            'users.update',
            'roles.update',
        ]);

        $actor = User::factory()->create();
        $actor->roles()->attach($actorRole);

        $target = User::factory()->create();

        Sanctum::actingAs($actor);

        $this->putJson("/api/users/{$target->id}", [
            'role_slugs' => ['admin'],
        ])->assertForbidden();

        $this->assertFalse($target->fresh()->hasRole('admin'));
    }

    public function test_authorized_user_can_update_target_roles(): void
    {
        Role::create([
            'name' => 'Gestor',
            'slug' => 'manager',
            'description' => 'Gestão de projetos.',
        ]);

        $actorRole = $this->createRoleWithPermissions([
            'users.update',
            'roles.update',
        ]);

        $actor = User::factory()->create();
        $actor->roles()->attach($actorRole);

        $target = User::factory()->create();

        Sanctum::actingAs($actor);

        $this->putJson("/api/users/{$target->id}", [
            'role_slugs' => ['manager'],
        ])
            ->assertOk()
            ->assertJsonPath('data.roles.0', 'manager');

        $this->assertTrue($target->fresh()->hasRole('manager'));
    }

    public function test_last_administrator_cannot_be_removed_from_own_account(): void
    {
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        Role::create([
            'name' => 'Membro',
            'slug' => 'member',
            'description' => 'Utilizador comum.',
        ]);

        $permissions = collect([
            'users.update',
            'roles.update',
        ])->map(fn (string $slug) => Permission::create([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'Permissão de teste.',
        ]));

        $adminRole->permissions()->sync($permissions->pluck('id'));

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$admin->id}", [
            'role_slugs' => ['member'],
        ])->assertUnprocessable();

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_last_administrator_cannot_be_deleted(): void
    {
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total.',
        ]);

        $permission = Permission::create([
            'name' => 'users.delete',
            'slug' => 'users.delete',
            'description' => 'Permissão de teste.',
        ]);

        $adminRole->permissions()->sync([$permission->id]);

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
