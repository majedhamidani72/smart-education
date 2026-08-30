<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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

            'app_grade_subject_id' => 'required|exists:app_grade_subjects,id',

            'title' => 'required|string|max:255',

            'slug' => 'required|string|max:255',

            'cover' => 'nullable|string|max:255',

            'academic_year' => 'required|string|max:10',

            'pages_count' => 'nullable|integer|min:1',

            'description' => 'nullable|string',

            'sort_order' => 'nullable|integer|min:1',

            'is_active' => 'boolean',

        ];
    }
}
