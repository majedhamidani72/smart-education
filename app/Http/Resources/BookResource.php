<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * تبدیل مدل به آرایه خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'grade_subject_id' => $this->grade_subject_id,

            'title' => $this->title,

            'slug' => $this->slug,

            'cover' => $this->cover,

            'academic_year' => $this->academic_year,

            'pages_count' => $this->pages_count,

            'description' => $this->description,

            'sort_order' => $this->sort_order,

            'is_active' => $this->is_active,

        ];
    }
}
