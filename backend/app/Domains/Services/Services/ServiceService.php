<?php

namespace App\Domains\Services\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    /**
     * Lista os serviços do catálogo.
     *
     * @return Collection<int, Service>
     */
    public function list(bool $includeInactive = false): Collection
    {
        $query = Service::query()
            ->orderBy('name');

        if (! $includeInactive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Cria um novo serviço no catálogo.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data): Service {
            $service = Service::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'default_cost' => $data['default_cost'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $service->fresh();
        });
    }

    /**
     * Atualiza um serviço existente.
     *
     * @param array<string, mixed> $data
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data): Service {
            $service->update($data);

            return $service->fresh();
        });
    }

    /**
     * Ativa um serviço do catálogo.
     */
    public function activate(Service $service): Service
    {
        $service->update([
            'is_active' => true,
        ]);

        return $service->fresh();
    }

    /**
     * Desativa um serviço do catálogo.
     */
    public function deactivate(Service $service): Service
    {
        $service->update([
            'is_active' => false,
        ]);

        return $service->fresh();
    }
}
