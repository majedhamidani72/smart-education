<?php

namespace App\Http\Requests\Subject;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
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
        $subjectId = $this->route('subject');

        return [

            'title' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'slug')->ignore($subjectId),
            ],

            'description' => 'nullable|string',

            'icon' => 'nullable|string|max:255',

            'sort_order' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
