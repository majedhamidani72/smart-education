<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * تبدیل مدل به خروجی API
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'video_url' => asset($this->file_path),

            'original_name' => $this->original_name,

            'mime_type' => $this->mime_type,

            'file_size' => $this->file_size,

            'duration' => $this->duration,

            'quality' => $this->quality,

            'thumbnail_url' => $this->thumbnail_path
                ? asset($this->thumbnail_path)
                : null,

            'views_count' => $this->views_count,

            'download_allowed' => $this->download_allowed,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
