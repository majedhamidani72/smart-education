<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'quizable_type' => [
                'required',
                'string',
            ],

            'quizable_id' => [
                'required',
                'integer',
            ],

            'created_by' => [
                'required',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'questions_count' => [
                'required',
                'integer',
                'min:1',
            ],

            'time_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'passing_percentage' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'max_attempts' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'randomize_questions' => [
                'required',
                'boolean',
            ],

            'randomize_options' => [
                'required',
                'boolean',
            ],

            'show_result' => [
                'required',
                'boolean',
            ],

            'show_correct_answers' => [
                'required',
                'boolean',
            ],

            'is_free' => [
                'required',
                'boolean',
            ],

            'status' => [
                'required',
                'string',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

        ];
    }
}
