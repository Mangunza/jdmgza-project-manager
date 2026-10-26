<?php

namespace App\Domains\Products\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity_before' => $this->quantity_before,
            'quantity_change' => $this->quantity_change,
            'quantity_after' => $this->quantity_after,
            'type' => $this->type,
            'reason' => $this->reason,
            'external_reference' => $this->external_reference,
            'responsible_user_id' => $this->responsible_user_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
