<?php

namespace App\Domains\Users\Controllers;

use App\Domains\Users\Requests\StoreUserRequest;
use App\Domains\Users\Requests\UpdateUserRequest;
use App\Domains\Users\Resources\UserResource;
use App\Domains\Users\Services\UserService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $users = $this->userService->list();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        if (
            $request->has('role_slugs')
            && ! $request->user()?->hasPermission('roles.update')
        ) {
            throw new AuthorizationException(
                'Não possui permissão para atribuir roles.',
            );
        }

        $user = $this->userService->create(
            $request->validated()
        );

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): UserResource {
        $user = $this->userService->update(
            $user,
            $request->validated()
        );

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json([
            'message' => 'Utilizador eliminado com sucesso.',
        ]);
    }
}
