<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'quiz_attempt_id' => $this->quiz_attempt_id,

            'question_id' => $this->question_id,

            'question_option_id' => $this->question_option_id,

            'is_correct' => $this->is_correct,

            'score_awarded' => $this->score_awarded,

            'question_snapshot' => $this->question_snapshot,

            'options_snapshot' => $this->options_snapshot,

            'answered_at' => $this->answered_at,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
