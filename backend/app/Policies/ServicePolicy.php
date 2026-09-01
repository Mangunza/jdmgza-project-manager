<?php

namespace App\Policies;

use App\Domains\Auth\Services\AuthorizationService;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'services.view',
        );
    }

    public function view(User $user, Service $service): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'services.view',
        );
    }

    public function create(User $user): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'services.create',
        );
    }

    public function update(User $user, Service $service): bool
    {
        return $this->authorizationService->userHasPermission(
            $user,
            'services.update',
        );
    }
}
