<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'book_id' => $this->book_id,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'sort_order' => $this->sort_order,

            'is_active' => $this->is_active,

        ];
    }
}
