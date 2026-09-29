<?php

namespace App\Domains\Auth\Services;

use App\Models\RecoveryContactVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use LogicException;

class RecoveryContactVerificationService
{
    private const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    /**
     * Emite e envia um código de verificação para o contacto de recuperação.
     *
     * Os canais permitidos são "email" e "sms".
     */
    public function issue(User $user, string $channel): void
    {
        if (! in_array($channel, ['email', 'sms'], true)) {
            throw new LogicException('Canal de verificação inválido.');
        }

        $destination = $this->getDestination($user, $channel);

        if (! $destination) {
            throw new LogicException('O contacto de recuperação não está definido.');
        }

        /*
         * O envio por SMS ainda necessita de um fornecedor configurado.
         * Não criamos um código que não possa ser entregue.
         */
        if ($channel === 'sms') {
            throw new LogicException(
                'O envio por SMS ainda não está configurado.'
            );
        }

        $code = (string) random_int(100000, 999999);

        RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->delete();

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => $channel,
            'destination_hash' => $this->hashDestination($destination),
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'attempts' => 0,
        ]);

        Mail::raw(
            "O teu código de verificação é: {$code}\n\n"
            . 'Este código expira em '
            . self::CODE_TTL_MINUTES
            . ' minutos. Se não solicitaste este código, ignora esta mensagem.',
            function ($message) use ($destination): void {
                $message
                    ->to($destination)
                    ->subject('Código de verificação');
            }
        );
    }

    /**
     * Valida o código e marca o contacto como verificado se for válido.
     */
    public function verify(User $user, string $channel, string $code): bool
    {
        if (! in_array($channel, ['email', 'sms'], true)) {
            return false;
        }

        $destination = $this->getDestination($user, $channel);

        if (! $destination) {
            return false;
        }

        return DB::transaction(function () use (
            $user,
            $channel,
            $code,
            $destination
        ): bool {
            $verification = RecoveryContactVerification::query()
                ->where('user_id', $user->id)
                ->where('channel', $channel)
                ->whereNull('verified_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $verification) {
                return false;
            }

            if (
                $verification->expires_at->isPast()
                || $verification->attempts >= self::MAX_ATTEMPTS
                || ! hash_equals(
                    (string) $verification->destination_hash,
                    $this->hashDestination($destination)
                )
            ) {
                return false;
            }

            $verification->increment('attempts');
            $verification->refresh();

            if (! Hash::check($code, $verification->code_hash)) {
                return false;
            }

            $verification->forceFill([
                'verified_at' => now(),
            ])->save();

            if ($channel === 'email') {
                $user->forceFill([
                    'recovery_email_verified_at' => now(),
                ])->save();
            } else {
                $user->forceFill([
                    'recovery_phone_verified_at' => now(),
                ])->save();
            }

            return true;
        });
    }

    /**
     * Obtém o contacto correspondente ao canal.
     */
    private function getDestination(User $user, string $channel): ?string
    {
        $destination = $channel === 'email'
            ? $user->recovery_email
            : $user->recovery_phone;

        if (! is_string($destination) || trim($destination) === '') {
            return null;
        }

        return trim($destination);
    }

    /**
     * Produz um hash consistente do destino.
     */
    private function hashDestination(string $destination): string
    {
        return hash('sha256', Str::lower(trim($destination)));
    }
}
