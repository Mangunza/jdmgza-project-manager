<?php

namespace App\Domains\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectServiceRequest extends FormRequest
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
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }
}
