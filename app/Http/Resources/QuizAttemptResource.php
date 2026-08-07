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

            'user_id' => $this->user_id,

            'quiz_id' => $this->quiz_id,

            'total_score' => $this->total_score,

            'earned_score' => $this->earned_score,

            'percentage' => $this->percentage,

            'correct_answers_count' => $this->correct_answers_count,

            'wrong_answers_count' => $this->wrong_answers_count,

            'unanswered_count' => $this->unanswered_count,

            'status' => $this->status,

            'started_at' => $this->started_at,

            'finished_at' => $this->finished_at,

            'duration_seconds' => $this->duration_seconds,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
