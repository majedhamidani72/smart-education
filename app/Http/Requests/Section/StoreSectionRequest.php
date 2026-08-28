<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('sections', 'sort_order')
                    ->where(fn ($query) => $query
                        ->where('chapter_id', $this->integer('chapter_id'))
                        ->whereNull('deleted_at')),
            ],

            'is_active' => 'sometimes|boolean',

        ];
    }
}
