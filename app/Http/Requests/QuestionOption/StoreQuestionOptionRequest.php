<?php

namespace App\Http\Requests\QuestionOption;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'question_id' => [
                'required',
                'exists:questions,id',
            ],

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
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:1',
            ],

        ];
    }
}
