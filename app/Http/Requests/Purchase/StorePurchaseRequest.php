<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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

            'invoice_number' => [
                'required',
                'string',
                'max:255',
                'unique:purchases,invoice_number',
            ],

            'total_amount' => [
                'required',
                'integer',
                'min:0',
            ],

            'discount_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'payable_amount' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:pending,paid,failed,cancelled,refunded',
            ],

            'paid_at' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

        ];
    }

    /**
     * پیام‌ها
     */
    public function messages(): array
    {
        return [

            'user_id.required' => 'کاربر الزامی است.',

            'user_id.exists' => 'کاربر معتبر نیست.',

            'invoice_number.required' => 'شماره فاکتور الزامی است.',

            'invoice_number.unique' => 'شماره فاکتور تکراری است.',

            'total_amount.required' => 'مبلغ کل الزامی است.',

            'payable_amount.required' => 'مبلغ قابل پرداخت الزامی است.',

            'status.required' => 'وضعیت پرداخت الزامی است.',

            'status.in' => 'وضعیت پرداخت نامعتبر است.',

        ];
    }
}
