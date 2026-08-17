<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SampleQuestionResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => $this->id,

            'content_item_id' => $this->content_item_id,

            'title' => $this->contentItem?->title,

            'page_number' => $this->contentItem?->page_number,

            'section_id' => $this->contentItem?->section_id,

            'directory' => $this->directory,

            'filename' => $this->filename,

            'original_name' => $this->original_name,

            'extension' => $this->extension,

            'mime_type' => $this->mime_type,

            'file_size' => $this->file_size,

            'file_size_readable' => $this->fileSizeReadable,

            'download_allowed' => $this->download_allowed,

            'processing_status' => $this->processing_status,

            'approved_at' => $this->approved_at,

            'rejected_reason' => $this->rejected_reason,

            'url' => $this->fileUrl,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];

    }
}
