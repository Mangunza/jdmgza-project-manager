<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicesApiTest extends TestCase
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

    public function test_unauthenticated_user_cannot_list_services(): void
    {
        $response = $this->getJson('/api/services');

        $response->assertUnauthorized();
    }

    public function test_user_without_view_permission_cannot_list_services(): void
    {
        $user = $this->createUserWithPermissions([]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/services');

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'Não autorizado.',
            ]);
    }

    public function test_user_with_view_permission_can_list_active_services(): void
    {
        $user = $this->createUserWithPermissions([
            'services.view',
        ]);

        Service::create([
            'name' => 'Desenvolvimento Web',
            'description' => 'Serviço ativo.',
            'default_cost' => 50000,
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Serviço Inativo',
            'description' => 'Não deve aparecer na listagem.',
            'default_cost' => 10000,
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/services');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'default_cost',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.name',
                'Desenvolvimento Web'
            )
            ->assertJsonPath(
                'data.0.default_cost',
                '50000.00'
            )
            ->assertJsonPath(
                'data.0.is_active',
                true
            );
    }

    public function test_user_with_view_permission_can_show_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.view',
        ]);

        $service = Service::create([
            'name' => 'Consultoria',
            'description' => 'Consultoria técnica.',
            'default_cost' => 75000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            "/api/services/{$service->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $service->id
            )
            ->assertJsonPath(
                'data.name',
                'Consultoria'
            )
            ->assertJsonPath(
                'data.default_cost',
                '75000.00'
            );
    }

    public function test_user_without_create_permission_cannot_create_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.view',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/services', [
            'name' => 'Serviço Não Autorizado',
            'description' => 'Não deve ser criado.',
            'default_cost' => 10000,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('services', [
            'name' => 'Serviço Não Autorizado',
        ]);
    }

    public function test_user_with_create_permission_can_create_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.create',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/services', [
            'name' => 'Desenvolvimento Mobile',
            'description' => 'Aplicação mobile personalizada.',
            'default_cost' => 125000,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.name',
                'Desenvolvimento Mobile'
            )
            ->assertJsonPath(
                'data.description',
                'Aplicação mobile personalizada.'
            )
            ->assertJsonPath(
                'data.default_cost',
                '125000.00'
            )
            ->assertJsonPath(
                'data.is_active',
                true
            );

        $this->assertDatabaseHas('services', [
            'name' => 'Desenvolvimento Mobile',
            'default_cost' => 125000,
            'is_active' => true,
        ]);
    }

    public function test_service_validation_rejects_invalid_data(): void
    {
        $user = $this->createUserWithPermissions([
            'services.create',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/services', [
            'name' => '',
            'default_cost' => -100,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'default_cost',
            ]);
    }

    public function test_user_without_update_permission_cannot_update_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.view',
        ]);

        $service = Service::create([
            'name' => 'Nome Original',
            'default_cost' => 10000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/services/{$service->id}",
            [
                'name' => 'Nome Indevido',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Nome Original',
        ]);
    }

    public function test_user_with_update_permission_can_update_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.update',
        ]);

        $service = Service::create([
            'name' => 'Nome Original',
            'description' => 'Descrição original.',
            'default_cost' => 10000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/services/{$service->id}",
            [
                'name' => 'Nome Atualizado',
                'description' => 'Descrição atualizada.',
                'default_cost' => 25000,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Nome Atualizado'
            )
            ->assertJsonPath(
                'data.description',
                'Descrição atualizada.'
            )
            ->assertJsonPath(
                'data.default_cost',
                '25000.00'
            );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Nome Atualizado',
            'default_cost' => 25000,
        ]);
    }

    public function test_user_with_update_permission_can_deactivate_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.update',
        ]);

        $service = Service::create([
            'name' => 'Serviço Ativo',
            'default_cost' => 30000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/services/{$service->id}/deactivate"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $service->id
            )
            ->assertJsonPath(
                'data.is_active',
                false
            );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => false,
        ]);
    }

    public function test_user_with_update_permission_can_activate_service(): void
    {
        $user = $this->createUserWithPermissions([
            'services.update',
        ]);

        $service = Service::create([
            'name' => 'Serviço Inativo',
            'default_cost' => 30000,
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/services/{$service->id}/activate"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $service->id
            )
            ->assertJsonPath(
                'data.is_active',
                true
            );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => true,
        ]);
    }

    public function test_delete_service_endpoint_does_not_exist(): void
    {
        $user = $this->createUserWithPermissions([
            'services.update',
        ]);

        $service = Service::create([
            'name' => 'Serviço Protegido',
            'default_cost' => 15000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/services/{$service->id}"
        );

        $response->assertMethodNotAllowed();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
        ]);
    }
}
