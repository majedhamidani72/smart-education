<?php

namespace App\Http\Requests\QuizAttempt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'earned_score' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'percentage' => [
                'sometimes',
                'numeric',
                'between:0,100',
            ],

            'correct_answers_count' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'wrong_answers_count' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'unanswered_count' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'status' => [
                'sometimes',
                'in:started,completed,cancelled',
            ],

            'finished_at' => [
                'nullable',
                'date',
            ],

            'duration_seconds' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ];
    }
}
