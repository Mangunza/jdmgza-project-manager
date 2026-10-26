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
        $isOwner = (int) $project->user_id === (int) $user->id;

        $isAdmin = $this->authorizationService->userHasRole(
            $user,
            'admin',
        );

        $isActiveMember = $project->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();

        return ($isAdmin || $isOwner || $isActiveMember)
            && $this->authorizationService->userHasPermission(
                $user,
                $permission,
            );
    }
}
