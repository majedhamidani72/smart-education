<?php

namespace App\Http\Requests\PaymentTransaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTransactionRequest extends FormRequest
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
                'sometimes',
                'required',
                'exists:purchases,id',
            ],

            'user_id' => [
                'sometimes',
                'required',
                'exists:users,id',
            ],

            'gateway' => [
                'sometimes',
                'required',
                'string',
                'max:30',
            ],

            'authority' => [
                'nullable',
                'string',
                'max:100',
            ],

            'transaction_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'reference_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'amount' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10',
            ],

            'status' => [
                'sometimes',
                'required',
                'in:pending,paid,failed,cancelled,refunded',
            ],

            'card_pan' => [
                'nullable',
                'string',
                'max:20',
            ],

            'message' => [
                'nullable',
                'string',
            ],

            'gateway_response' => [
                'nullable',
                'array',
            ],

            'paid_at' => [
                'nullable',
                'date',
            ],

            'verified_at' => [
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

            'purchase_id.required' => 'شناسه خرید الزامی است.',
            'purchase_id.exists' => 'خرید معتبر نیست.',

            'user_id.required' => 'شناسه کاربر الزامی است.',
            'user_id.exists' => 'کاربر معتبر نیست.',

            'gateway.required' => 'درگاه پرداخت الزامی است.',

            'amount.required' => 'مبلغ الزامی است.',

            'status.required' => 'وضعیت تراکنش الزامی است.',
            'status.in' => 'وضعیت تراکنش معتبر نیست.',

        ];
    }
}
