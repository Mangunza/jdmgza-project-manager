<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyRecoveryContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'channel' => [
                'required',
                'string',
                Rule::in(['email', 'sms']),
            ],

            'code' => [
                'required',
                'string',
                'regex:/^\d{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required' => 'O canal de recuperação é obrigatório.',
            'channel.in' => 'O canal de recuperação deve ser email ou sms.',
            'code.required' => 'O código de verificação é obrigatório.',
            'code.regex' => 'O código de verificação deve conter exatamente 6 dígitos.',
        ];
    }
}
