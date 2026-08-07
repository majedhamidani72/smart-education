<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'login_token' => [

                'required',

                'string',

                'size:64',

            ],

        ];
    }

    public function messages(): array
    {
        return [

            'login_token.required' => 'Login Token الزامی است.',

            'login_token.size' => 'Login Token معتبر نیست.',

        ];
    }
}
