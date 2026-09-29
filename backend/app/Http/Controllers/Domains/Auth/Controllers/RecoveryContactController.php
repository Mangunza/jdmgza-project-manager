<?php

namespace App\Http\Controllers\Domains\Auth\Controllers;

use App\Domains\Auth\Services\RecoveryContactVerificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestRecoveryContactVerificationRequest;
use App\Http\Requests\UpdateRecoveryContactsRequest;
use App\Http\Requests\VerifyRecoveryContactRequest;
use App\Models\RecoveryContactVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RecoveryContactController extends Controller
{
    public function update(UpdateRecoveryContactsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            foreach ([
                'recovery_email' => 'email',
                'recovery_phone' => 'sms',
            ] as $field => $channel) {
                if (! array_key_exists($field, $validated)) {
                    continue;
                }

                $newValue = $validated[$field];

                if (is_string($newValue)) {
                    $newValue = trim($newValue);

                    if ($field === 'recovery_email') {
                        $newValue = strtolower($newValue);
                    }
                }

                if ($user->{$field} === $newValue) {
                    continue;
                }

                $user->{$field} = $newValue;

                $verifiedAtField = $field === 'recovery_email'
                    ? 'recovery_email_verified_at'
                    : 'recovery_phone_verified_at';

                $user->{$verifiedAtField} = null;

                RecoveryContactVerification::query()
                    ->where('user_id', $user->id)
                    ->where('channel', $channel)
                    ->whereNull('verified_at')
                    ->delete();
            }

            $user->save();
        });

        $user->refresh();

        return response()->json([
            'message' => 'Contactos de recuperação atualizados com sucesso.',
            'data' => [
                'recovery_email' => $user->recovery_email,
                'recovery_email_verified_at' => $user->recovery_email_verified_at,
                'recovery_phone' => $user->recovery_phone,
                'recovery_phone_verified_at' => $user->recovery_phone_verified_at,
            ],
        ]);
    }

    public function requestVerification(
        RequestRecoveryContactVerificationRequest $request,
        RecoveryContactVerificationService $service
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $service->issue(
            $user,
            $request->validated('channel')
        );

        return response()->json([
            'message' => 'Código de verificação enviado com sucesso.',
        ]);
    }

    public function verify(
        VerifyRecoveryContactRequest $request,
        RecoveryContactVerificationService $service
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $verified = $service->verify(
            $user,
            $request->validated('channel'),
            $request->validated('code')
        );

        if (! $verified) {
            return response()->json([
                'message' => 'Código de verificação inválido ou expirado.',
            ], 422);
        }

        return response()->json([
            'message' => 'Contacto de recuperação verificado com sucesso.',
        ]);
    }
}
