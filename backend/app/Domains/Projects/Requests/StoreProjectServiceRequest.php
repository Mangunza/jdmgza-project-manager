<?php

namespace App\Domains\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('project')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'uuid',
                Rule::exists('services', 'id'),
            ],
            'quantity' => [
                'sometimes',
                'numeric',
                'gt:0',
            ],
        ];
    }
}
