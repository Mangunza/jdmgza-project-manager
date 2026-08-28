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

            if (! $category->is_active) {
                throw new LogicException(
                    'Não é possível criar um projeto numa categoria inativa.',
                );
            }

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
}
