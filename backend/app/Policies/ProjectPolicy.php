<?php

namespace App\Policies;

use App\Domains\Auth\Services\AuthorizationService;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'projects.view',
        );
    }

    public function view(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            && $this->authorizationService->userHasPermission(
                $user,
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
        return $project->user_id === $user->id
            && $this->authorizationService->userHasPermission(
                $user,
                'projects.update',
            );
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            && $this->authorizationService->userHasPermission(
                $user,
                'projects.delete',
            );
    }
}
