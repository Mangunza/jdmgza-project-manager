<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles atribuídos ao utilizador.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Verifica se o utilizador possui um determinado role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('slug', $role)
            ->exists();
    }

    /**
     * Verifica se o utilizador possui pelo menos um dos roles.
     *
     * @param array<string> $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()
            ->whereIn('slug', $roles)
            ->exists();
    }

    /**
     * Verifica se o utilizador possui uma determinada permissão.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas(
                'permissions',
                fn ($query) => $query->where('slug', $permission),
            )
            ->exists();
    }

    /**
     * Verifica se o utilizador possui pelo menos uma das permissões.
     *
     * @param array<string> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()
            ->whereHas(
                'permissions',
                fn ($query) => $query->whereIn('slug', $permissions),
            )
            ->exists();
    }
}
