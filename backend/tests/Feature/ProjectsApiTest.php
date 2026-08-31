<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsApiTest extends TestCase
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

    public function test_unauthenticated_user_cannot_list_projects(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertUnauthorized();
    }

    public function test_user_without_view_permission_cannot_list_projects(): void
    {
        $user = $this->createUserWithPermissions([]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/projects');

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'Não autorizado.',
            ]);
    }

    public function test_user_with_view_permission_can_list_own_projects(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $category = Category::factory()->create();

        Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Meu Projeto',
        ]);

        Project::factory()->create([
            'category_id' => $category->id,
            'name' => 'Projeto de Outro Utilizador',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/projects');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.name',
                'Meu Projeto'
            );
    }

    public function test_user_with_create_permission_can_create_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.create',
        ]);

        $category = Category::factory()->create([
            'name' => 'Desenvolvimento Web',
            'slug' => 'desenvolvimento-web',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'category_id' => $category->id,
            'name' => 'Sistema de Gestão',
            'description' => 'Projeto de teste.',
            'total_budget' => 150000.00,
            'delivery_date' => '2026-12-31',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.name',
                'Sistema de Gestão'
            )
            ->assertJsonPath(
                'data.description',
                'Projeto de teste.'
            )
            ->assertJsonPath(
                'data.total_budget',
                '150000.00'
            )
            ->assertJsonPath(
                'data.total_cost',
                '0.00'
            )
            ->assertJsonPath(
                'data.category.id',
                $category->id
            )
            ->assertJsonPath(
                'data.category.slug',
                'desenvolvimento-web'
            )
            ->assertJsonPath(
                'data.status',
                'draft'
            );

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Sistema de Gestão',
            'total_budget' => 150000.00,
            'total_cost' => 0,
            'status' => 'draft',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_project(): void
    {
        $user = $this->createUserWithPermissions([]);

        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'category_id' => $category->id,
            'name' => 'Projeto Não Autorizado',
            'total_budget' => 10000,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('projects', [
            'name' => 'Projeto Não Autorizado',
        ]);
    }

    public function test_project_cannot_be_created_with_inactive_category(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.create',
        ]);

        $category = Category::factory()->inactive()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'category_id' => $category->id,
            'name' => 'Projeto Categoria Inativa',
            'total_budget' => 10000,
        ]);

        $response->assertStatus(500);

        $this->assertDatabaseMissing('projects', [
            'name' => 'Projeto Categoria Inativa',
        ]);
    }

    public function test_project_validation_rejects_invalid_data(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.create',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'category_id' => 999999,
            'name' => '',
            'total_budget' => -100,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_id',
                'name',
                'total_budget',
            ]);
    }

    public function test_owner_can_view_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/projects/{$project->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $project->id
            )
            ->assertJsonPath(
                'data.name',
                $project->name
            );
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $otherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/projects/{$project->id}"
        );

        $response->assertForbidden();
    }

    public function test_owner_can_update_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Nome Antigo',
            'total_budget' => 10000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            [
                'name' => 'Nome Atualizado',
                'total_budget' => 25000,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Nome Atualizado'
            )
            ->assertJsonPath(
                'data.total_budget',
                '25000.00'
            );

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $user->id,
            'name' => 'Nome Atualizado',
            'total_budget' => 25000,
        ]);
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $otherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Projeto Original',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            [
                'name' => 'Alteração Indevida',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Projeto Original',
        ]);
    }

    public function test_user_without_update_permission_cannot_update_project(): void
    {
        $user = $this->createUserWithPermissions([]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Projeto Original',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/projects/{$project->id}",
            [
                'name' => 'Alteração Não Autorizada',
            ]
        );

        $response->assertForbidden();
    }

    public function test_owner_can_delete_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.delete',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}"
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.delete',
        ]);

        $otherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_user_without_delete_permission_cannot_delete_project(): void
    {
        $user = $this->createUserWithPermissions([]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }
}
