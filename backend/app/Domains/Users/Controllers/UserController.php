<?php

namespace App\Domains\Users\Controllers;

use App\Domains\Users\Requests\StoreUserRequest;
use App\Domains\Users\Requests\UpdateUserRequest;
use App\Domains\Users\Resources\UserResource;
use App\Domains\Users\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            $this->userService->list()
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create(
            $request->validated(),
            $request->user()
        );

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $user->load('roles.permissions');

        return new UserResource($user);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): UserResource {
        $updatedUser = $this->userService->update(
            $user,
            $request->validated(),
            $request->user()
        );

        return new UserResource($updatedUser);
    }

    public function destroy(
        User $user,
        \Illuminate\Http\Request $request
    ): JsonResponse {
        $this->userService->delete(
            $user,
            $request->user()
        );

        return response()->json([
            'message' => 'Utilizador eliminado com sucesso.',
        ]);
    }
}
