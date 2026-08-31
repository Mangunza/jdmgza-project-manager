<?php

namespace App\Domains\Projects\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'create',
            Project::class
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'total_budget' => [
                'required',
                'numeric',
                'min:0',
            ],

            'delivery_date' => [
                'nullable',
                'date',
            ],
        ];
    }
}
