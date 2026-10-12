<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Enums\ProjectBudgetStatus;
use App\Models\Project;

class ProjectBudgetCalculator
{
    /**
     * Calcula a percentagem do orçamento utilizado,
     * arredondada para apresentação.
     */
    public function percentage(Project $project): float
    {
        return round($this->rawPercentage($project), 2);
    }

    /**
     * Determina o estado com base na percentagem não arredondada.
     */
    public function status(Project $project): ProjectBudgetStatus
    {
        $budget = (float) $project->total_budget;

        if ($budget <= 0) {
            return ProjectBudgetStatus::NO_BUDGET;
        }

        $percentage = $this->rawPercentage($project);

        return match (true) {
            $percentage >= 100 => ProjectBudgetStatus::EXCEEDED,
            $percentage >= 90 => ProjectBudgetStatus::CRITICAL,
            $percentage >= 80 => ProjectBudgetStatus::WARNING,
            default => ProjectBudgetStatus::NORMAL,
        };
    }

    /**
     * Calcula a percentagem sem arredondamento intermédio.
     */
    private function rawPercentage(Project $project): float
    {
        $budget = (float) $project->total_budget;
        $cost = (float) $project->total_cost;

        if ($budget <= 0) {
            return 0.0;
        }

        return ($cost / $budget) * 100;
    }
}
