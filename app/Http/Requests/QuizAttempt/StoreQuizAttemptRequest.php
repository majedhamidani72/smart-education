<?php

namespace App\Http\Requests\QuizAttempt;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'user_id' => [
                'required',
                'exists:users,id',
            ],

            'quiz_id' => [
                'required',
                'exists:quizzes,id',
            ],

            'total_score' => [
                'required',
                'integer',
                'min:0',
            ],

            'earned_score' => [
                'required',
                'integer',
                'min:0',
            ],

            'percentage' => [
                'required',
                'numeric',
                'between:0,100',
            ],

            'correct_answers_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'wrong_answers_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'unanswered_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                'in:started,completed,cancelled',
            ],

            'started_at' => [
                'nullable',
                'date',
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
