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


            'result' => [

                'is_correct' => $this->is_correct,

                'score_awarded' => $this->score_awarded,

            ],


            'question_snapshot' => $this->question_snapshot,

            'options_snapshot' => $this->options_snapshot,


            'question' => $this->whenLoaded(
                'question',
                function () {
                    return [
                        'id' => $this->question->id,
                        'text' => $this->question->question_text,
                        'difficulty' => $this->question->difficulty,
                    ];
                }
            ),


            'selected_option' => $this->whenLoaded(
                'selectedOption',
                function () {
                    return [
                        'id' => $this->selectedOption->id,
                        'text' => $this->selectedOption->option_text,
                    ];
                }
            ),


            'answered_at' => $this->answered_at
                ? $this->answered_at->format('Y-m-d H:i:s')
                : null,


            'created_at' => $this->created_at
                ? $this->created_at->format('Y-m-d H:i:s')
                : null,


            'updated_at' => $this->updated_at
                ? $this->updated_at->format('Y-m-d H:i:s')
                : null,

        ];
    }
}
