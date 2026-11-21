<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientInvitation extends Model
{
    protected $fillable = [
        'client_id',
        'email',
        'token_hash',
        'invited_by',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Verifica se o convite ainda pode ser utilizado.
     *
     * Um convite só é utilizável quando:
     * - ainda não foi aceite;
     * - não foi revogado;
     * - tem uma data de expiração válida;
     * - ainda não expirou.
     */
    public function isUsable(): bool
    {
        return $this->accepted_at === null
            && $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
