<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * اجازه دسترسی
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

            'user_id' => [
                'required',
                'exists:users,id',
            ],

            'purchase_id' => [
                'required',
                'exists:purchases,id',
            ],

            'plan_id' => [
                'required',
                'exists:plans,id',
            ],

            'status' => [
                'required',
                'in:active,expired,cancelled',
            ],

            'starts_at' => [
                'required',
                'date',
            ],

            'expires_at' => [
                'required',
                'date',
                'after:starts_at',
            ],

            'cancelled_at' => [
                'nullable',
                'date',
            ],

        ];
    }

    /**
     * پیام‌های اعتبارسنجی
     */
    public function messages(): array
    {
        return [

            'user_id.required' => 'کاربر الزامی است.',
            'user_id.exists' => 'کاربر معتبر نیست.',

            'purchase_id.required' => 'خرید الزامی است.',
            'purchase_id.exists' => 'خرید معتبر نیست.',

            'plan_id.required' => 'پلن الزامی است.',
            'plan_id.exists' => 'پلن معتبر نیست.',

            'status.required' => 'وضعیت اشتراک الزامی است.',
            'status.in' => 'وضعیت اشتراک معتبر نیست.',

            'starts_at.required' => 'تاریخ شروع الزامی است.',

            'expires_at.required' => 'تاریخ پایان الزامی است.',
            'expires_at.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',

        ];
    }
}
