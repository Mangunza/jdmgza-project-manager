<?php

namespace Tests\Feature;

use App\Domains\Projects\Services\ProjectServiceManager;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectServiceManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_associates_an_active_service_with_a_project(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Desenvolvimento Web',
            'description' => 'Criação de website',
            'default_cost' => 50000,
            'is_active' => true,
        ]);

        $projectService = app(ProjectServiceManager::class)->add(
            $project,
            [
                'service_id' => $service->id,
                'quantity' => 2,
            ],
        );

        $this->assertInstanceOf(ProjectService::class, $projectService);

        $this->assertSame($service->id, $projectService->service_id);
        $this->assertSame('Desenvolvimento Web', $projectService->name);
        $this->assertSame('Criação de website', $projectService->description);
        $this->assertSame('2.00', $projectService->quantity);
        $this->assertSame('50000.00', $projectService->unit_cost);
        $this->assertSame('100000.00', $projectService->total_cost);

        $this->assertSame(
            '100000.00',
            $project->fresh()->total_cost,
        );
    }

    public function test_it_defaults_quantity_to_one(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'UI/UX Design',
            'description' => null,
            'default_cost' => 75000,
            'is_active' => true,
        ]);

        $projectService = app(ProjectServiceManager::class)->add(
            $project,
            [
                'service_id' => $service->id,
            ],
        );

        $this->assertSame('1.00', $projectService->quantity);
        $this->assertSame('75000.00', $projectService->total_cost);
    }

    public function test_it_rejects_an_inactive_service(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Serviço Inativo',
            'description' => null,
            'default_cost' => 50000,
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(ProjectServiceManager::class)->add(
            $project,
            [
                'service_id' => $service->id,
                'quantity' => 1,
            ],
        );
    }

    public function test_it_rejects_duplicate_service_in_same_project(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Backend API',
            'description' => null,
            'default_cost' => 100000,
            'is_active' => true,
        ]);

        $manager = app(ProjectServiceManager::class);

        $manager->add($project, [
            'service_id' => $service->id,
            'quantity' => 1,
        ]);

        $this->expectException(ValidationException::class);

        $manager->add($project, [
            'service_id' => $service->id,
            'quantity' => 2,
        ]);
    }

    public function test_it_creates_a_snapshot_of_the_service_price(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Consultoria',
            'description' => 'Consultoria técnica',
            'default_cost' => 80000,
            'is_active' => true,
        ]);

        $manager = app(ProjectServiceManager::class);

        $projectService = $manager->add($project, [
            'service_id' => $service->id,
            'quantity' => 1,
        ]);

        $service->update([
            'name' => 'Consultoria Premium',
            'description' => 'Nova descrição',
            'default_cost' => 120000,
        ]);

        $projectService->refresh();

        $this->assertSame('Consultoria', $projectService->name);
        $this->assertSame('Consultoria técnica', $projectService->description);
        $this->assertSame('80000.00', $projectService->unit_cost);
        $this->assertSame('80000.00', $projectService->total_cost);
    }

    public function test_it_updates_quantity_and_recalculates_costs(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 500000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Mobile App',
            'description' => null,
            'default_cost' => 100000,
            'is_active' => true,
        ]);

        $manager = app(ProjectServiceManager::class);

        $projectService = $manager->add($project, [
            'service_id' => $service->id,
            'quantity' => 2,
        ]);

        $updated = $manager->update(
            $projectService,
            [
                'quantity' => 3,
            ],
        );

        $this->assertSame('3.00', $updated->quantity);
        $this->assertSame('100000.00', $updated->unit_cost);
        $this->assertSame('300000.00', $updated->total_cost);

        $this->assertSame(
            '300000.00',
            $project->fresh()->total_cost,
        );
    }

    public function test_it_removes_service_and_recalculates_project_total(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 1000000,
            'total_cost' => 0,
        ]);

        $serviceOne = Service::create([
            'name' => 'Website',
            'description' => null,
            'default_cost' => 150000,
            'is_active' => true,
        ]);

        $serviceTwo = Service::create([
            'name' => 'Mobile App',
            'description' => null,
            'default_cost' => 300000,
            'is_active' => true,
        ]);

        $manager = app(ProjectServiceManager::class);

        $projectServiceOne = $manager->add($project, [
            'service_id' => $serviceOne->id,
            'quantity' => 1,
        ]);

        $manager->add($project, [
            'service_id' => $serviceTwo->id,
            'quantity' => 1,
        ]);

        $this->assertSame(
            '450000.00',
            $project->fresh()->total_cost,
        );

        $manager->remove($projectServiceOne);

        $this->assertSame(
            '300000.00',
            $project->fresh()->total_cost,
        );

        $this->assertDatabaseMissing('project_services', [
            'id' => $projectServiceOne->id,
        ]);
    }
}
