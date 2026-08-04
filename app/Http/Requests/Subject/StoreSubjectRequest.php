<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    /**
     * بررسی مجوز ارسال درخواست
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

            'title' => 'required|string|max:255',

            'slug' => 'required|string|max:255|unique:subjects,slug',

            'description' => 'nullable|string',

            'icon' => 'nullable|string|max:255',

            'sort_order' => 'nullable|integer|min:1',

            'is_active' => 'boolean',

        ];
    }
}
