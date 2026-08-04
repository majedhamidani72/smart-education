<?php

namespace App\Http\Requests\Chapter;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $chapter = $this->route('chapter');

        return [

            'book_id' => 'required|exists:books,id',

            'title' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chapters', 'slug')
                    ->where(fn ($query) => $query->where('book_id', $this->book_id))
                    ->ignore($chapter),
            ],

            'description' => 'nullable|string',

            'sort_order' => 'sometimes|integer|min:1',

            'is_active' => 'sometimes|boolean',

        ];
    }
}
