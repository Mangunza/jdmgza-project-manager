<?php

namespace App\Policies;

use App\Domains\Auth\Services\AuthorizationService;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->authorizationService->userHasPermission($user, 'products.view');
    }

    public function viewInactive(User $user): bool
    {
        return $this->authorizationService->userHasPermission($user, 'products.view_inactive');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user)
            && ($product->is_active || $this->viewInactive($user));
    }

    public function create(User $user): bool
    {
        return $this->authorizationService->userHasPermission($user, 'products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->authorizationService->userHasPermission($user, 'products.update');
    }
}
