<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    /**
     * آیا کاربر اجازه ارسال این درخواست را دارد؟
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create grades') ?? false;
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

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:grades,slug',
            ],

            'grade_number' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                'unique:grades,grade_number',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'is_active' => [
                'boolean',
            ],

        ];
    }
}
