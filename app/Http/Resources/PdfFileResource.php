<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class PdfFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'title' => $this->contentItem?->title,

            'file' => URL::temporarySignedRoute('pdf-files.view', now()->addHours(2), ['pdfFile' => $this->id]),

            'view_url' => URL::temporarySignedRoute('pdf-files.view', now()->addHours(2), ['pdfFile' => $this->id]),

            'original_name' => $this->original_name,

            'file_size' => $this->file_size,

            'download_allowed' => false,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
