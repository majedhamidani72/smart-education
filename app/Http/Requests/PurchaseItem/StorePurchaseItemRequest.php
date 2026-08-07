<?php

namespace App\Http\Requests\PurchaseItem;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseItemRequest extends FormRequest
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

            'purchase_id' => [
                'required',
                'exists:purchases,id',
            ],

            'item_type' => [
                'required',
                'string',
                'max:255',
            ],

            'item_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            'discount_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'final_price' => [
                'required',
                'integer',
                'min:0',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'notes' => [
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

            'purchase_id.required' => 'شناسه خرید الزامی است.',
            'purchase_id.exists' => 'خرید یافت نشد.',

            'item_type.required' => 'نوع آیتم الزامی است.',

            'item_id.required' => 'شناسه آیتم الزامی است.',

            'title.required' => 'عنوان آیتم الزامی است.',

            'price.required' => 'قیمت الزامی است.',

            'final_price.required' => 'قیمت نهایی الزامی است.',

            'quantity.required' => 'تعداد الزامی است.',

        ];
    }
}
