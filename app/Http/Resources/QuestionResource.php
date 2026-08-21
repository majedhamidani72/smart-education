<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'question_text' => $this->question_text,

            'image_path' => $this->image_path
                ? Storage::url($this->image_path)
                : null,

            'explanation' => $this->explanation,

            'explanation_image_path' => $this->explanation_image_path
                ? Storage::url($this->explanation_image_path)
                : null,

            'recommendation_text' => $this->recommendation_text,


            'difficulty' => $this->difficulty,

            'default_score' => $this->default_score,


            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,


            'is_active' => $this->is_active,


            'topic' => $this->whenLoaded(
                'topic',
                function () {
                    return [
                        'id' => $this->topic->id,
                        'title' => $this->topic->title,
                    ];
                }
            ),


            'creator' => $this->whenLoaded(
                'creator',
                function () {
                    return [
                        'id' => $this->creator->id,
                        'name' => $this->creator->name,
                    ];
                }
            ),


            'reviewer' => $this->whenLoaded(
                'reviewer',
                function () {
                    return [
                        'id' => $this->reviewer->id,
                        'name' => $this->reviewer->name,
                    ];
                }
            ),


            'options' => QuestionOptionResource::collection(
                $this->whenLoaded('options')
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
