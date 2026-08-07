<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'questions_count' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'time_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'passing_percentage' => [
                'sometimes',
                'integer',
                'between:0,100',
            ],

            'max_attempts' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'randomize_questions' => [
                'sometimes',
                'boolean',
            ],

            'randomize_options' => [
                'sometimes',
                'boolean',
            ],

            'show_result' => [
                'sometimes',
                'boolean',
            ],

            'show_correct_answers' => [
                'sometimes',
                'boolean',
            ],

            'is_free' => [
                'sometimes',
                'boolean',
            ],

            'status' => [
                'sometimes',
                'string',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

        ];
    }
}
