<?php

namespace App\Domains\Clients\Controllers;

use App\Domains\Clients\Requests\AcceptClientInvitationRequest;
use App\Domains\Clients\Services\ClientInvitationService;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientInvitationController extends Controller
{
    public function __construct(
        private readonly ClientInvitationService $invitationService,
    ) {}

    /**
     * Emite um convite para o email registado no cliente.
     */
    public function store(Request $request, Client $client): JsonResponse
    {
        $invitation = $this->invitationService->send(
            $client,
            $request->user(),
        );

        return response()->json([
            'message' => 'Convite enviado com sucesso.',
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'expires_at' => $invitation->expires_at,
            ],
        ], 201);
    }

    /**
     * Aceita um convite e cria a conta de cliente.
     */
    public function accept(
        AcceptClientInvitationRequest $request,
    ): JsonResponse {
        $result = $this->invitationService->accept(
            $request->validated(),
        );

        return response()->json([
            'message' => 'Convite aceite. Conta criada com sucesso.',
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
                'roles' => $result['user']->roles->pluck('slug'),
                'permissions' => $result['user']->roles
                    ->flatMap(
                        fn ($role) => $role->permissions->pluck('slug')
                    )
                    ->unique()
                    ->values(),
            ],
            'token' => $result['token'],
        ], 201);
    }
}
