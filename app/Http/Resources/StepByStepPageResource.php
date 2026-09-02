<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepByStepPageResource extends JsonResource
{
    /**
     * تبدیل مدل به خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'page_number' => $this->page_number,

            'image' => $this->image
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image)
                : null,

            'sort_order' => $this->sort_order,

            'is_free' => $this->is_free,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
