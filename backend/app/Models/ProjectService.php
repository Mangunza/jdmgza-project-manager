<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectService extends Model
{
    protected $table = 'project_services';

    protected $fillable = [
        'project_id',
        'service_id',
        'name',
        'description',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Projeto ao qual este serviço pertence.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Serviço do catálogo utilizado no projeto.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
