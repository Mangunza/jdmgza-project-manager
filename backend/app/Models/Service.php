<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'default_cost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Linhas de orçamento que utilizam este serviço.
     */
    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }
}
