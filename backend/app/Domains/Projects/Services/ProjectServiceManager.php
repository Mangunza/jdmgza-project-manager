<?php

namespace App\Domains\Projects\Services;

use App\Models\Project;
use App\Models\ProjectService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectServiceManager
{
    /**
     * Associa um serviço do catálogo a um projeto.
     *
     * O preço e os dados descritivos são copiados do catálogo
     * para preservar o histórico do projeto.
     *
     * @param array<string, mixed> $data
     */
    public function add(
        Project $project,
        array $data,
    ): ProjectService {
        return DB::transaction(function () use ($project, $data): ProjectService {
            $service = Service::query()
                ->findOrFail($data['service_id']);

            $this->ensureServiceIsActive($service);
            $this->ensureServiceIsNotAlreadyAssociated($project, $service);

            $quantity = $data['quantity'] ?? 1;
            $unitCost = (float) $service->default_cost;
            $totalCost = $quantity * $unitCost;

            $projectService = $project->projectServices()->create([
                'service_id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);

            $this->recalculateProjectTotalCost($project);

            return $projectService->fresh([
                'service',
            ]);
        });
    }

    /**
     * Atualiza a quantidade de um serviço já associado.
     *
     * O unit_cost permanece sendo o preço congelado no momento
     * da associação.
     *
     * @param array<string, mixed> $data
     */
    public function update(
        ProjectService $projectService,
        array $data,
    ): ProjectService {
        return DB::transaction(function () use ($projectService, $data): ProjectService {
            if (array_key_exists('quantity', $data)) {
                $quantity = (float) $data['quantity'];
                $unitCost = (float) $projectService->unit_cost;

                $projectService->quantity = $quantity;
                $projectService->total_cost = $quantity * $unitCost;
            }

            $projectService->save();

            $this->recalculateProjectTotalCost(
                $projectService->project,
            );

            return $projectService->fresh([
                'service',
            ]);
        });
    }

    /**
     * Remove um serviço associado ao projeto.
     */
    public function remove(ProjectService $projectService): void
    {
        DB::transaction(function () use ($projectService): void {
            $project = $projectService->project;

            $projectService->delete();

            $this->recalculateProjectTotalCost($project);
        });
    }

    private function ensureServiceIsActive(Service $service): void
    {
        if (! $service->is_active) {
            throw ValidationException::withMessages([
                'service_id' => 'Não é possível associar um serviço inativo.',
            ]);
        }
    }

    private function ensureServiceIsNotAlreadyAssociated(
        Project $project,
        Service $service,
    ): void {
        $alreadyAssociated = $project->projectServices()
            ->where('service_id', $service->id)
            ->exists();

        if ($alreadyAssociated) {
            throw ValidationException::withMessages([
                'service_id' => 'Este serviço já está associado ao projeto.',
            ]);
        }
    }

    private function recalculateProjectTotalCost(Project $project): void
    {
        $totalCost = $project->projectServices()
            ->sum('total_cost');

        $project->update([
            'total_cost' => $totalCost,
        ]);
    }
}
