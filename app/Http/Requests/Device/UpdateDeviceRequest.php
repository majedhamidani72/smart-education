<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    /**
     * اجازه اجرای درخواست
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [

            'device_name' => [

                'sometimes',

                'string',

                'max:150',

            ],

            'manufacturer' => [

                'nullable',

                'string',

                'max:100',

            ],

            'model' => [

                'nullable',

                'string',

                'max:100',

            ],

            'platform' => [

                'sometimes',

                'in:android,ios,web',

            ],

            'os_version' => [

                'nullable',

                'string',

                'max:50',

            ],

            'app_version' => [

                'nullable',

                'string',

                'max:50',

            ],

            'fcm_token' => [

                'nullable',

                'string',

            ],

            'is_active' => [

                'sometimes',

                'boolean',

            ],

        ];
    }

    /**
     * پیام‌های اعتبارسنجی
     */
    public function messages(): array
    {
        return [

            'device_name.max' => 'نام دستگاه حداکثر ۱۵۰ کاراکتر است.',

            'platform.in' => 'پلتفرم انتخاب شده معتبر نیست.',

            'is_active.boolean' => 'مقدار وضعیت دستگاه نامعتبر است.',

        ];
    }
}
