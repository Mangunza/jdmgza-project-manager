<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecoveryContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'recovery_email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'recovery_email')->ignore($userId),
            ],

            'recovery_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s().-]+$/',
                Rule::unique('users', 'recovery_phone')->ignore($userId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recovery_email.email' => 'O email de recuperação deve ser um endereço válido.',
            'recovery_email.unique' => 'Este email de recuperação já está associado a outra conta.',
            'recovery_phone.regex' => 'O telefone de recuperação contém caracteres inválidos.',
            'recovery_phone.max' => 'O telefone de recuperação não pode exceder 20 caracteres.',
            'recovery_phone.unique' => 'Este telefone de recuperação já está associado a outra conta.',
        ];
    }
}
