<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepByStepPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'page_number' => $this->page_number,

            'image' => asset($this->image),

            'sort_order' => $this->sort_order,

            'is_free' => $this->is_free,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
