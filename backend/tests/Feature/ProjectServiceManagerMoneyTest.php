<?php

namespace Tests\Feature;

use App\Domains\Projects\Services\ProjectServiceManager;
use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceManagerMoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_fractional_costs_are_rounded_and_project_total_is_recalculated(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'total_budget' => 1000,
            'total_cost' => 0,
        ]);

        $service = Service::create([
            'name' => 'Serviço fracionário',
            'description' => null,
            'default_cost' => 10.25,
            'is_active' => true,
        ]);

        $manager = app(ProjectServiceManager::class);

        $projectService = $manager->add($project, [
            'service_id' => $service->id,
            'quantity' => 3,
        ]);

        $this->assertSame('10.25', $projectService->unit_cost);
        $this->assertSame('30.75', $projectService->total_cost);
        $this->assertSame('30.75', $project->fresh()->total_cost);

        $updated = $manager->update($projectService, [
            'quantity' => 1.5,
        ]);

        $this->assertSame('1.50', $updated->quantity);
        $this->assertSame('10.25', $updated->unit_cost);
        $this->assertSame('15.38', $updated->total_cost);
        $this->assertSame('15.38', $project->fresh()->total_cost);
    }
}
