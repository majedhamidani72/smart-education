<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'planable_type' => [
                'required',
                'string',
                'max:255',
            ],

            'planable_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            'discount_price' => [
                'nullable',
                'integer',
                'min:0',
                'lte:price',
            ],

            'purchase_type' => [
                'required',
                'in:one_time,subscription',
            ],

            'duration_days' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after:starts_at',
            ],

        ];
    }

    /**
     * پیام‌ها
     */
    public function messages(): array
    {
        return [

            'title.required' => 'عنوان پلن الزامی است.',

            'planable_type.required' => 'نوع محصول الزامی است.',

            'planable_id.required' => 'شناسه محصول الزامی است.',

            'price.required' => 'قیمت الزامی است.',

            'discount_price.lte' => 'قیمت تخفیف نباید بیشتر از قیمت اصلی باشد.',

            'purchase_type.required' => 'نوع خرید الزامی است.',

            'purchase_type.in' => 'نوع خرید نامعتبر است.',

            'expires_at.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',

        ];
    }
}
