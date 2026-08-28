<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_cost',
        'is_active',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

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
