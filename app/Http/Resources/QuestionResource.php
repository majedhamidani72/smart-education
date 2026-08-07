<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'created_by' => $this->created_by,

            'reviewed_by' => $this->reviewed_by,

            'question_text' => $this->question_text,

            'image_path' => $this->image_path
                ? asset($this->image_path)
                : null,

            'explanation' => $this->explanation,

            'explanation_image_path' => $this->explanation_image_path
                ? asset($this->explanation_image_path)
                : null,

            'difficulty' => $this->difficulty,

            'default_score' => $this->default_score,

            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,

            'is_active' => $this->is_active,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
