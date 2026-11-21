<?php

namespace App\Policies;

use App\Domains\Auth\Services\AuthorizationService;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'projects.view',
        );
    }

    public function view(User $user, Project $project): bool
    {
        return $this->canAccessProject(
            $user,
            $project,
            'projects.view',
        );
    }

    public function create(User $user): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'projects.create',
        );
    }

    public function update(User $user, Project $project): bool
    {
        return $this->canAccessProject(
            $user,
            $project,
            'projects.update',
        );
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->canAccessProject(
            $user,
            $project,
            'projects.delete',
        );
    }

    private function canAccessProject(
        User $user,
        Project $project,
        string $permission,
    ): bool {
        if (! $this->authorizationService->userHasPermission(
            $user,
            $permission,
        )) {
            return false;
        }

        if ($this->authorizationService->userHasRole($user, 'admin')) {
            return true;
        }

        /*
         * Uma conta com papel client só pode consultar projetos
         * explicitamente autorizados para o respetivo registo.
         *
         * Não pode obter acesso por ser proprietária ou membro
         * de um projeto.
         */
        if ($this->authorizationService->userHasRole($user, 'client')) {
            if ($permission !== 'projects.view') {
                return false;
            }

            $client = $user->client()
                ->where('is_active', true)
                ->first();

            if (! $client) {
                return false;
            }

            return $project->clientAccesses()
                ->where('client_id', $client->id)
                ->where('status', 'active')
                ->exists();
        }

        $isOwner = (int) $project->user_id === (int) $user->id;

        if ($isOwner) {
            return true;
        }

        return $project->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
