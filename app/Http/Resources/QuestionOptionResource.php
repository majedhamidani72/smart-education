<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'question_id' => $this->question_id,

            'option_text' => $this->option_text,

            'image_path' => $this->image_path
                ? asset($this->image_path)
                : null,

            'is_correct' => $this->is_correct,

            'sort_order' => $this->sort_order,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
