<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'questions_count' => $this->questions_count,

            'time_limit' => $this->time_limit,

            'passing_percentage' => $this->passing_percentage,

            'max_attempts' => $this->max_attempts,

            'randomize_questions' => $this->randomize_questions,

            'randomize_options' => $this->randomize_options,

            'show_result' => $this->show_result,

            'show_correct_answers' => $this->show_correct_answers,

            'is_free' => $this->is_free,

            'status' => $this->status,

            'published_at' => $this->published_at,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
