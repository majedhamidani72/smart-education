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


            'settings' => [

                'randomize_questions' => $this->randomize_questions,

                'randomize_options' => $this->randomize_options,

                'show_result' => $this->show_result,

                'show_correct_answers' => $this->show_correct_answers,

            ],


            'is_free' => $this->is_free,

            'status' => $this->status,


            'published_at' => $this->published_at
                ? $this->published_at->format('Y-m-d H:i:s')
                : null,


            'creator' => $this->whenLoaded(
                'creator',
                function () {
                    return [
                        'id' => $this->creator->id,
                        'name' => $this->creator->name,
                    ];
                }
            ),


            'target' => $this->whenLoaded(
                'quizable',
                function () {
                    return [
                        'type' => class_basename($this->quizable_type),
                        'id' => $this->quizable_id,
                        'title' => $this->quizable->title ?? null,
                    ];
                }
            ),


            'questions' => QuestionResource::collection(
                $this->whenLoaded('questions')
            ),


            'created_at' => $this->created_at
                ? $this->created_at->format('Y-m-d H:i:s')
                : null,


            'updated_at' => $this->updated_at
                ? $this->updated_at->format('Y-m-d H:i:s')
                : null,

        ];
    }
}
