<?php

namespace App\Domains\Auth\Controllers;

use Illuminate\Http\JsonResponse;

class AuthorizationTestController
{
    public function usersCreate(): JsonResponse
    {
        return response()->json([
            'message' => 'Permissão users.create validada com sucesso.',
        ]);
    }
}
