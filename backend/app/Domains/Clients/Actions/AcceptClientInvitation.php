<?php

namespace App\Domains\Clients\Actions;

use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AcceptClientInvitation
{
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $invitation = ClientInvitation::query()
                ->where(
                    'token_hash',
                    hash('sha256', $data['token']),
                )
                ->lockForUpdate()
                ->first();

            if (! $invitation || ! $invitation->isUsable()) {
                throw ValidationException::withMessages([
                    'token' => [
                        'O convite é inválido, expirou ou já foi utilizado.',
                    ],
                ]);
            }

            $client = Client::query()
                ->whereKey($invitation->client_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $client) {
                throw ValidationException::withMessages([
                    'token' => [
                        'O cliente associado ao convite não está ativo.',
                    ],
                ]);
            }

            if (
                $client->user_id !== null
                || User::where('email', $invitation->email)->exists()
            ) {
                throw ValidationException::withMessages([
                    'email' => [
                        'Já existe uma conta associada a este email ou cliente.',
                    ],
                ]);
            }

            if (
                mb_strtolower($client->email ?? '')
                !== mb_strtolower($invitation->email)
            ) {
                throw ValidationException::withMessages([
                    'email' => [
                        'O email do cliente já não corresponde ao convite.',
                    ],
                ]);
            }

            $clientRole = Role::where('slug', 'client')->first();

            if (! $clientRole) {
                throw new \RuntimeException(
                    'A role client ainda não foi criada. '
                    . 'Executa o seeder de permissões.',
                );
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
            ]);

            $user->roles()->sync([$clientRole->id]);

            $client->user()->associate($user);
            $client->save();

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $plainTextToken = $user
                ->createToken('client-portal')
                ->plainTextToken;

            return [
                'user' => $user->load('roles.permissions'),
                'token' => $plainTextToken,
            ];
        });
    }
}
