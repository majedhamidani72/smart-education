<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * تبدیل مدل به خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'chapter_id' => $this->chapter_id,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'sort_order' => $this->sort_order,

            'is_active' => $this->is_active,

        ];
    }
}
