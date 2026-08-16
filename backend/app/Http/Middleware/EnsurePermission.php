<?php

namespace App\Http\Middleware;

use App\Domains\Auth\Services\AuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(
        private readonly AuthorizationService $authorizationService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        if (
            ! $this->authorizationService->userHasPermission(
                $user,
                $permission
            )
        ) {
            return response()->json([
                'message' => 'Não autorizado.',
            ], 403);
        }

        return $next($request);
    }
}
