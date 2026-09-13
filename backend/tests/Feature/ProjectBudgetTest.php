<?php

namespace Tests\Feature;

use App\Domains\Projects\Enums\ProjectBudgetStatus;
use App\Domains\Projects\Services\ProjectBudgetCalculator;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBudgetTest extends TestCase
{
    use RefreshDatabase;

    private ProjectBudgetCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = app(ProjectBudgetCalculator::class);
    }

    public function test_project_without_budget_has_no_budget_status(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 0,
            'total_cost' => 0,
        ]);

        $this->assertSame(
            0.0,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::NO_BUDGET,
            $this->calculator->status($project)
        );
    }

    public function test_project_below_80_percent_has_normal_status(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 100000,
            'total_cost' => 79900,
        ]);

        $this->assertSame(
            79.9,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::NORMAL,
            $this->calculator->status($project)
        );
    }

    public function test_project_at_80_percent_has_warning_status(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 100000,
            'total_cost' => 80000,
        ]);

        $this->assertSame(
            80.0,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::WARNING,
            $this->calculator->status($project)
        );
    }

    public function test_project_at_90_percent_has_critical_status(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 100000,
            'total_cost' => 90000,
        ]);

        $this->assertSame(
            90.0,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::CRITICAL,
            $this->calculator->status($project)
        );
    }

    public function test_project_at_100_percent_has_exceeded_status(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 100000,
            'total_cost' => 100000,
        ]);

        $this->assertSame(
            100.0,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::EXCEEDED,
            $this->calculator->status($project)
        );
    }

    public function test_project_above_100_percent_remains_exceeded(): void
    {
        $project = Project::factory()->create([
            'total_budget' => 100000,
            'total_cost' => 150000,
        ]);

        $this->assertSame(
            150.0,
            $this->calculator->percentage($project)
        );

        $this->assertSame(
            ProjectBudgetStatus::EXCEEDED,
            $this->calculator->status($project)
        );
    }
}
