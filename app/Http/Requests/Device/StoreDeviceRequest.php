<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
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

            'device_identifier' => [

                'required',

                'string',

                'max:150',

            ],

            'device_name' => [

                'required',

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

                'required',

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

        ];
    }

    /**
     * پیام‌های اعتبارسنجی
     */
    public function messages(): array
    {
        return [

            'device_identifier.required' => 'شناسه دستگاه الزامی است.',

            'device_name.required' => 'نام دستگاه الزامی است.',

            'platform.required' => 'نوع پلتفرم الزامی است.',

            'platform.in' => 'پلتفرم نامعتبر است.',

        ];
    }
}
