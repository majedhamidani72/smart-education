<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'reviewed_by' => [
                'nullable',
                'exists:users,id',
            ],

            'question_text' => [
                'sometimes',
                'string',
            ],

            'image_path' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'explanation' => [
                'nullable',
                'string',
            ],

            'explanation_image_path' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'difficulty' => [
                'sometimes',
                'in:easy,medium,hard',
            ],

            'default_score' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'status' => [
                'sometimes',
                'in:draft,pending,approved,rejected',
            ],

            'rejection_reason' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

        ];
    }
}
