<?php

namespace App\Domains\Users\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = $this->whenLoaded('roles');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,

            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->pluck('slug')
                    ->unique()
                    ->values()
            ),

            'permissions' => $this->when(
                $this->relationLoaded('roles')
                    && $this->roles->every(
                        fn ($role) => $role->relationLoaded('permissions')
                    ),
                fn () => $this->roles
                    ->flatMap(
                        fn ($role) => $role->permissions->pluck('slug')
                    )
                    ->unique()
                    ->sort()
                    ->values()
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
