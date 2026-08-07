<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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

            'code' => [

                'required',

                'digits:6',

            ],

        ];
    }

    public function messages(): array
    {
        return [

            'login_token.required' => 'توکن ورود الزامی است.',

            'login_token.size' => 'توکن ورود نامعتبر است.',

            'code.required' => 'کد تایید الزامی است.',

            'code.digits' => 'کد تایید باید ۶ رقم باشد.',

        ];
    }
}
