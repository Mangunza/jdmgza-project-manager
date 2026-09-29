<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestRecoveryContactVerificationRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required' => 'O canal de recuperação é obrigatório.',
            'channel.in' => 'O canal de recuperação deve ser email ou sms.',
        ];
    }
}
