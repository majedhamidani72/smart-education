<?php

declare(strict_types=1);

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class RejectVideoRequest extends FormRequest
{
    /**
     * مجوز ارسال درخواست
     */
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->can('videos.reject');
    }


    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [

            'rejected_reason' => [

                'required',

                'string',

                'min:5',

                'max:1000',

            ],

        ];
    }


    /**
     * پیام‌های خطا
     */
    public function messages(): array
    {
        return [

            'rejected_reason.required'
                => 'دلیل رد کردن ویدئو الزامی است.',


            'rejected_reason.min'
                => 'دلیل رد کردن باید حداقل ۵ کاراکتر باشد.',


            'rejected_reason.max'
                => 'دلیل رد کردن نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',

        ];
    }
}
