<?php

namespace App\Domains\Clients\Services;

use App\Domains\Clients\Actions\AcceptClientInvitation;
use App\Domains\Clients\Actions\SendClientInvitation;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\User;

class ClientInvitationService
{
    public function __construct(
        private readonly SendClientInvitation $sendInvitation,
        private readonly AcceptClientInvitation $acceptInvitation,
    ) {}

    public function send(
        Client $client,
        User $invitedBy,
    ): ClientInvitation {
        return $this->sendInvitation->execute(
            $client,
            $invitedBy,
        );
    }

    public function accept(array $data): array
    {
        return $this->acceptInvitation->execute($data);
    }
}
