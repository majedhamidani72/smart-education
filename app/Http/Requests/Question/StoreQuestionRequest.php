<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'created_by' => [
                'required',
                'exists:users,id',
            ],

            'reviewed_by' => [
                'nullable',
                'exists:users,id',
            ],

            'question_text' => [
                'nullable',
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
                'required',
                'in:easy,medium,hard',
            ],

            'default_score' => [
                'required',
                'integer',
                'min:1',
            ],

            'status' => [
                'required',
                'in:draft,pending,approved,rejected',
            ],

            'rejection_reason' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ];
    }
}
