<?php

namespace App\Models;

use App\Domains\Projects\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'total_budget',
        'total_cost',
        'delivery_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_budget' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'delivery_date' => 'date',
            'status' => ProjectStatus::class,
        ];
    }

    /**
     * Utilizador proprietário do projeto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Categoria do projeto.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Serviços associados ao projeto.
     */
    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }
}
