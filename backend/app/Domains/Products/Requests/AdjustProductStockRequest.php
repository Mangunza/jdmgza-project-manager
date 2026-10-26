<?php

namespace App\Domains\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustProductStockRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'not_in:0'],
            'type' => ['nullable', 'in:adjustment,entry,exit,return'],
            'reason' => ['nullable', 'string', 'max:500'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
