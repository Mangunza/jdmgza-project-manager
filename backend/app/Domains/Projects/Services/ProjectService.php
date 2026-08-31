<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class ProjectService
{
    /**
     * Cria um novo projeto para o utilizador informado.
     *
     * @param array<string, mixed> $data
     */
    public function create(User $user, array $data): Project
    {
        return DB::transaction(function () use ($user, $data): Project {
            $category = Category::query()
                ->findOrFail($data['category_id']);

            $this->ensureCategoryIsActive($category);

            $project = Project::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'total_budget' => $data['total_budget'],
                'total_cost' => 0,
                'delivery_date' => $data['delivery_date'] ?? null,
                'status' => ProjectStatus::DRAFT,
            ]);

            return $project->fresh([
                'user',
                'category',
            ]);
        });
    }

    /**
     * Atualiza um projeto existente.
     *
     * @param array<string, mixed> $data
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data): Project {
            if (isset($data['category_id'])) {
                $category = Category::query()
                    ->findOrFail($data['category_id']);

                $this->ensureCategoryIsActive($category);
            }

            $project->fill([
                'category_id' => $data['category_id'] ?? $project->category_id,
                'name' => $data['name'] ?? $project->name,
                'description' => array_key_exists('description', $data)
                    ? $data['description']
                    : $project->description,
                'total_budget' => $data['total_budget'] ?? $project->total_budget,
                'delivery_date' => array_key_exists('delivery_date', $data)
                    ? $data['delivery_date']
                    : $project->delivery_date,
            ]);

            $project->save();

            return $project->fresh([
                'user',
                'category',
            ]);
        });
    }

    /**
     * Remove um projeto.
     */
    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project): void {
            $project->delete();
        });
    }

    private function ensureCategoryIsActive(Category $category): void
    {
        if (! $category->is_active) {
            throw new LogicException(
                'Não é possível utilizar uma categoria inativa.',
            );
        }
    }
}
