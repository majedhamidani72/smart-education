<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PdfFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'title' => $this->title,

            'file' => asset($this->file),

            'file_size' => $this->file_size,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
