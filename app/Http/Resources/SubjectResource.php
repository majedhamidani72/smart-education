<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    /**
     * تبدیل مدل به آرایه خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'icon' => $this->icon,

            'sort_order' => $this->sort_order,

            'is_active' => $this->is_active,

        ];
    }
}
