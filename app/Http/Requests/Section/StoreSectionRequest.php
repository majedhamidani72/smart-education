<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    /**
     * مجوز ارسال درخواست
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

            'chapter_id' => 'required|exists:chapters,id',

            'title' => 'required|string|max:255',

            'slug' => 'required|string|max:255',

            'description' => 'nullable|string',

            'sort_order' => 'sometimes|integer|min:1',

            'is_active' => 'sometimes|boolean',

        ];
    }
}
