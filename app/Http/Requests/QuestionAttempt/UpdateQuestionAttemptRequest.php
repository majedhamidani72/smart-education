<?php

namespace App\Http\Requests\QuestionAttempt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'question_option_id' => [
                'nullable',
                'exists:question_options,id',
            ],

            'is_correct' => [
                'nullable',
                'boolean',
            ],

            'score_awarded' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'question_snapshot' => [
                'nullable',
                'array',
            ],

            'options_snapshot' => [
                'nullable',
                'array',
            ],

            'answered_at' => [
                'nullable',
                'date',
            ],

        ];
    }
}
