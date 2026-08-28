<?php

namespace Tests\Feature;

use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Services\ProjectService;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'name' => 'Desenvolvimento Web',
            'slug' => 'desenvolvimento-web',
            'description' => 'Projetos de desenvolvimento web.',
            'is_active' => true,
        ]);

        $service = new ProjectService();

        $project = $service->create($user, [
            'category_id' => $category->id,
            'name' => 'Website institucional',
            'description' => 'Criação de website institucional.',
            'total_budget' => 500000,
            'delivery_date' => '2026-12-31',
        ]);

        $this->assertInstanceOf(Project::class, $project);

        $this->assertSame($user->id, $project->user_id);
        $this->assertSame($category->id, $project->category_id);
        $this->assertSame('Website institucional', $project->name);
        $this->assertSame('Criação de website institucional.', $project->description);

        $this->assertSame('500000.00', $project->total_budget);
        $this->assertSame('0.00', $project->total_cost);

        $this->assertSame(
            ProjectStatus::DRAFT,
            $project->status,
        );

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Website institucional',
            'status' => 'draft',
        ]);
    }

    public function test_cannot_create_project_with_inactive_category(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'name' => 'Categoria Inativa',
            'slug' => 'categoria-inativa',
            'description' => 'Categoria utilizada para testar bloqueio.',
            'is_active' => false,
        ]);

        $service = new ProjectService();

        try {
            $service->create($user, [
                'category_id' => $category->id,
                'name' => 'Projeto bloqueado',
                'description' => 'Este projeto não deve ser criado.',
                'total_budget' => 500000,
                'delivery_date' => '2026-12-31',
            ]);

            $this->fail('Era esperada uma LogicException.');
        } catch (LogicException $exception) {
            $this->assertSame(
                'Não é possível criar um projeto numa categoria inativa.',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseCount('projects', 0);
    }
}
