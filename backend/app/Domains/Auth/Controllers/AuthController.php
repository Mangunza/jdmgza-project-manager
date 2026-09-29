<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Auth\Requests\ForgotPasswordRequest;
use App\Domains\Auth\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated()
        );

        return response()->json([
            'message' => 'Utilizador registado com sucesso.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Login efetuado com sucesso.',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(
            $request->user()
        );

        return response()->json([
            'message' => 'Logout efetuado com sucesso.',
        ]);
    }

    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => 'Se existir uma conta associada a este e-mail, receberás instruções para redefinir a senha.',
        ]);
    }

    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token',
            ),
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Não foi possível redefinir a senha. O link pode ser inválido ou ter expirado.',
            ], 422);
        }

        return response()->json([
            'message' => 'Senha redefinida com sucesso. Já podes iniciar sessão.',
        ]);
    }
}
