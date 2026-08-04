<?php

namespace App\Http\Requests\Section;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
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
        $section = $this->route('section');

        return [

            'chapter_id' => 'required|exists:chapters,id',

            'title' => 'required|string|max:255',

            'slug' => [

                'required',

                'string',

                'max:255',

                Rule::unique('sections', 'slug')
                    ->where(fn ($query) => $query->where(
                        'chapter_id',
                        $this->chapter_id
                    ))
                    ->ignore($section),

            ],

            'description' => 'nullable|string',

            'sort_order' => 'sometimes|integer|min:1',

            'is_active' => 'sometimes|boolean',

        ];
    }
}
