<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            'mobile' => [

                'required',

                'regex:/^09[0-9]{9}$/',

            ],

        ];
    }

    /**
     * Validation Messages
     */
    public function messages(): array
    {
        return [

            'mobile.required' => 'شماره موبایل الزامی است.',

            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست.',

        ];
    }
}
