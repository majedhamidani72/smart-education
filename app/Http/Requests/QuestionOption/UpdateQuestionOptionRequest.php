<?php

namespace App\Http\Requests\QuestionOption;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'option_text' => [
                'sometimes',
                'string',
            ],

            'image_path' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'is_correct' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
            ],

        ];
    }
}
