<?php

namespace Database\Factories;

use App\Domains\Projects\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'total_budget' => fake()->randomFloat(2, 1000, 100000),
            'total_cost' => 0,
            'delivery_date' => fake()->optional()->date(),
            'status' => ProjectStatus::DRAFT,
        ];
    }
}
