<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $questionSnapshot = $this->question_snapshot;
        if (! empty($questionSnapshot['image_path'])) {
            $questionSnapshot['image_path'] = $this->publicUrl($questionSnapshot['image_path']);
        }

        $optionsSnapshot = collect($this->options_snapshot ?? [])->map(function (array $option) {
            if (! empty($option['image_path'])) {
                $option['image_path'] = $this->publicUrl($option['image_path']);
            }

            return $option;
        })->values()->all();

        return [

            'id' => $this->id,

            'quiz_attempt_id' => $this->quiz_attempt_id,

            'question_id' => $this->question_id,

            'question_option_id' => $this->question_option_id,


            'result' => [

                'is_correct' => $this->is_correct,

                'score_awarded' => $this->score_awarded,

                // فقط بعد از ثبت پاسخ فرستاده می‌شود تا بازخورد
                // فوری بدون افشای پاسخ پیش از انتخاب ممکن باشد.
                'correct_option_id' => $this->answered_at
                    ? $this->question?->options()->where('is_correct', true)->value('id')
                    : null,

            ],


            'question_snapshot' => $questionSnapshot,

            'options_snapshot' => $optionsSnapshot,


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

    private function publicUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}
