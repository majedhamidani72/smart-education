<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentItemResource extends JsonResource
{
    /**
     * تبدیل مدل به خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'section_id' => $this->section_id,

            'content_type_id' => $this->content_type_id,

            'created_by' => $this->created_by,

            'reviewed_by' => $this->reviewed_by,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'page_number' => $this->page_number,

            'thumbnail' => $this->thumbnail,

            'is_free' => $this->is_free,

            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,

            'sort_order' => $this->sort_order,

            'published_at' => $this->published_at,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
