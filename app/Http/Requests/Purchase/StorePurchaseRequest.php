<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ثبت خرید
 * --------------------------------------------------------------------
 * قبلاً کلاینت مستقیم user_id، مبلغ‌ها، شماره‌ی فاکتور و وضعیت
 * را می‌فرستاد — یعنی هرکسی می‌توانست برای کاربر دیگری خرید ثبت
 * کند یا قیمت را خودش دستکاری کند. حالا کلاینت فقط می‌گوید
 * «کدام پلن(ها) را می‌خواهم»؛ کاربر، قیمت، شماره‌ی فاکتور و
 * وضعیت اولیه، همگی سمت سرور (در PurchaseService) محاسبه/تعیین
 * می‌شوند.
 */
class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'plan_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'plan_ids.*' => [
                'required',
                'integer',
                'exists:plans,id',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'plan_ids.required' => 'حداقل یک پلن باید انتخاب شود.',

            'plan_ids.*.exists' => 'پلن انتخاب‌شده معتبر نیست.',

        ];
    }
}
