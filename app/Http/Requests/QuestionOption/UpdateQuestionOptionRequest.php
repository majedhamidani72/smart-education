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
                'required_without:image_path',
                'nullable',
                'string',
            ],

            'image_path' => [
                'required_without:option_text',
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
