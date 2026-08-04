<?php

namespace App\Http\Requests\Chapter;

use Illuminate\Foundation\Http\FormRequest;

class StoreChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'book_id' => 'required|exists:books,id',

            'title' => 'required|string|max:255',

            'slug' => 'required|string|max:255',

            'description' => 'nullable|string',

            'sort_order' => 'sometimes|integer|min:1',

            'is_active' => 'sometimes|boolean',

        ];
    }
}
