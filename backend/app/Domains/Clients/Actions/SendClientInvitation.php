<?php

namespace App\Domains\Clients\Actions;

use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendClientInvitation
{
    public function execute(
        Client $client,
        User $invitedBy,
    ): ClientInvitation {
        abort_unless(
            $client->is_active,
            422,
            'O cliente está inativo.',
        );

        if (! $client->email) {
            throw ValidationException::withMessages([
                'email' => ['O cliente não tem um email registado.'],
            ]);
        }

        if ($client->user_id !== null) {
            throw ValidationException::withMessages([
                'client' => ['Este cliente já tem uma conta associada.'],
            ]);
        }

        if (User::where('email', $client->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Já existe uma conta com o email deste cliente.'],
            ]);
        }

        $token = Str::random(64);

        $invitation = DB::transaction(function () use (
            $client,
            $invitedBy,
            $token,
        ): ClientInvitation {
            ClientInvitation::query()
                ->where('client_id', $client->id)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                ]);

            return ClientInvitation::create([
                'client_id' => $client->id,
                'email' => $client->email,
                'token_hash' => hash('sha256', $token),
                'invited_by' => $invitedBy->id,
                'expires_at' => now()->addHours(48),
            ]);
        });

        $frontendUrl = rtrim(
            (string) env('FRONTEND_URL', config('app.url')),
            '/',
        );

        $acceptUrl = $frontendUrl
            . '/accept-invitation?token='
            . urlencode($token);

        Mail::raw(
            "Foste convidado para aceder ao portal de cliente.\n\n"
            . "Aceita o convite através deste endereço:\n"
            . $acceptUrl
            . "\n\nEste convite expira em 48 horas.",
            function ($message) use ($client): void {
                $message
                    ->to($client->email)
                    ->subject('Convite para o portal de cliente');
            },
        );

        return $invitation;
    }
}
