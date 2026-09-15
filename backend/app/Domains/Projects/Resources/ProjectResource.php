<?php

namespace App\Domains\Projects\Resources;

use App\Domains\Projects\Services\ProjectBudgetCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $budgetCalculator = app(ProjectBudgetCalculator::class);

        return [
            'id' => $this->id,

            'name' => $this->name,

            'description' => $this->description,

            'category' => $this->whenLoaded(
                'category',
                fn () => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ]
            ),

            'total_budget' => $this->total_budget,

            'total_cost' => $this->total_cost,

            'budget_percentage' => $budgetCalculator->percentage($this->resource),

            'budget_status' => $budgetCalculator->status($this->resource)->value,

            'delivery_date' => $this->delivery_date?->format('Y-m-d'),

            'status' => $this->status?->value,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
