<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectServiceApiTest extends TestCase
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
            fn (string $slug) => Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug,
                    'description' => 'Permissão de teste.',
                ],
            )
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

    private function createService(array $attributes = []): Service
    {
        return Service::create(array_merge([
            'name' => 'Desenvolvimento Web',
            'description' => 'Criação de aplicação web.',
            'default_cost' => 100000.00,
            'is_active' => true,
        ], $attributes));
    }

    public function test_unauthenticated_user_cannot_add_service_to_project(): void
    {
        $project = Project::factory()->create();
        $service = $this->createService();

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_user_without_update_permission_cannot_add_service_to_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_owner_with_update_permission_can_add_active_service_to_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'total_cost' => 0,
        ]);

        $service = $this->createService([
            'name' => 'Desenvolvimento Web',
            'description' => 'Aplicação web personalizada.',
            'default_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
                'quantity' => 2,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.service_id',
                $service->id
            )
            ->assertJsonPath(
                'data.name',
                'Desenvolvimento Web'
            )
            ->assertJsonPath(
                'data.description',
                'Aplicação web personalizada.'
            )
            ->assertJsonPath(
                'data.quantity',
                '2.00'
            )
            ->assertJsonPath(
                'data.unit_cost',
                '100000.00'
            )
            ->assertJsonPath(
                'data.total_cost',
                '200000.00'
            );

        $this->assertDatabaseHas('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => 'Desenvolvimento Web',
            'description' => 'Aplicação web personalizada.',
            'quantity' => 2,
            'unit_cost' => 100000.00,
            'total_cost' => 200000.00,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'total_cost' => 200000.00,
        ]);
    }

    public function test_quantity_defaults_to_one(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService([
            'default_cost' => 50000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.quantity',
                '1.00'
            )
            ->assertJsonPath(
                'data.total_cost',
                '50000.00'
            );

        $this->assertDatabaseHas('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'total_cost' => 50000.00,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'total_cost' => 50000.00,
        ]);
    }

    public function test_inactive_service_cannot_be_added_to_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService([
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'service_id',
            ]);

        $this->assertDatabaseMissing('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_same_service_cannot_be_added_twice_to_same_project(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService();

        Sanctum::actingAs($user);

        $firstResponse = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $firstResponse->assertOk();

        $secondResponse = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $secondResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'service_id',
            ]);

        $this->assertDatabaseCount('project_services', 1);
    }

    public function test_project_service_list_can_be_viewed_by_owner_with_view_permission(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService();

        $projectService = ProjectService::create([
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/projects/{$project->id}/services"
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.id',
                $projectService->id
            );
    }

    public function test_owner_can_view_specific_project_service(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.view',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService();

        $projectService = ProjectService::create([
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/projects/{$project->id}/services/{$projectService->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $projectService->id
            )
            ->assertJsonPath(
                'data.service_id',
                $service->id
            );
    }

    public function test_owner_can_update_project_service_quantity(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'total_cost' => 100000.00,
        ]);

        $service = $this->createService([
            'default_cost' => 100000.00,
        ]);

        $projectService = ProjectService::create([
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/projects/{$project->id}/services/{$projectService->id}",
            [
                'quantity' => 3,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.quantity',
                '3.00'
            )
            ->assertJsonPath(
                'data.unit_cost',
                '100000.00'
            )
            ->assertJsonPath(
                'data.total_cost',
                '300000.00'
            );

        $this->assertDatabaseHas('project_services', [
            'id' => $projectService->id,
            'quantity' => 3,
            'unit_cost' => 100000.00,
            'total_cost' => 300000.00,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'total_cost' => 300000.00,
        ]);
    }

    public function test_update_rejects_invalid_quantity(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService();

        $projectService = ProjectService::create([
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/projects/{$project->id}/services/{$projectService->id}",
            [
                'quantity' => 0,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'quantity',
            ]);
    }

    public function test_owner_can_remove_project_service(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'total_cost' => 200000.00,
        ]);

        $service = $this->createService();

        $projectService = ProjectService::create([
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 2,
            'unit_cost' => 100000.00,
            'total_cost' => 200000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/projects/{$project->id}/services/{$projectService->id}"
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('project_services', [
            'id' => $projectService->id,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'total_cost' => 0,
        ]);
    }

    public function test_user_cannot_access_project_service_from_another_users_project(): void
    {
        $owner = $this->createUserWithPermissions([
            'projects.view',
            'projects.update',
        ]);

        $otherUser = $this->createUserWithPermissions([
            'projects.view',
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $service = $this->createService();

        $projectService = ProjectService::create([
            'project_id' => $otherProject->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'quantity' => 1,
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson(
            "/api/projects/{$project->id}/services/{$projectService->id}"
        );

        $response->assertNotFound();
    }

    public function test_service_snapshot_is_preserved_when_catalog_service_changes(): void
    {
        $user = $this->createUserWithPermissions([
            'projects.update',
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $service = $this->createService([
            'name' => 'Desenvolvimento Web',
            'description' => 'Descrição original.',
            'default_cost' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/services",
            [
                'service_id' => $service->id,
            ]
        );

        $response->assertOk();

        $service->update([
            'name' => 'Desenvolvimento Web Premium',
            'description' => 'Nova descrição.',
            'default_cost' => 250000.00,
        ]);

        $this->assertDatabaseHas('project_services', [
            'project_id' => $project->id,
            'service_id' => $service->id,
            'name' => 'Desenvolvimento Web',
            'description' => 'Descrição original.',
            'unit_cost' => 100000.00,
            'total_cost' => 100000.00,
        ]);
    }
}
