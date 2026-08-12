<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'quiz_id' => $this->quiz_id,

            'user_id' => $this->user_id,


            'score' => [

                'total' => $this->total_score,

                'earned' => $this->earned_score,

                'percentage' => $this->percentage,

            ],


            'statistics' => [

                'correct_answers' => $this->correct_answers_count,

                'wrong_answers' => $this->wrong_answers_count,

                'unanswered' => $this->unanswered_count,

            ],


            'status' => $this->status,


            'duration_seconds' => $this->duration_seconds,


            'started_at' => $this->started_at
                ? $this->started_at->format('Y-m-d H:i:s')
                : null,


            'finished_at' => $this->finished_at
                ? $this->finished_at->format('Y-m-d H:i:s')
                : null,


            'quiz' => $this->whenLoaded(
                'quiz',
                function () {
                    return [
                        'id' => $this->quiz->id,
                        'title' => $this->quiz->title,
                    ];
                }
            ),


            'user' => $this->whenLoaded(
                'user',
                function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ];
                }
            ),


            'answers' => QuestionAttemptResource::collection(
                $this->whenLoaded('questionAttempts')
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
