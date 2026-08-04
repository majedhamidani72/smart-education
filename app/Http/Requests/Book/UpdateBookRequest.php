<?php

namespace App\Http\Requests\Book;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
        $book = $this->route('book');

        return [

            'grade_subject_id' => 'required|exists:grade_subject,id',

            'title' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('books', 'slug')
                    ->where(fn ($query) => $query->where('grade_subject_id', $this->grade_subject_id))
                    ->ignore($book),
            ],

            'cover' => 'nullable|string|max:255',

            'academic_year' => 'nullable|string|max:20',

            'pages_count' => 'nullable|integer|min:1',

            'description' => 'nullable|string',

            'sort_order' => 'nullable|integer|min:1',

            'is_active' => 'boolean',

        ];
    }
}
