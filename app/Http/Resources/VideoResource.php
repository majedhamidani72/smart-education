<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,


            /*
            |--------------------------------------------------------------------------
            | Educational Structure
            |--------------------------------------------------------------------------
            */

            'content_item' => [

                'id' => $this->whenLoaded(
                    'contentItem',
                    fn () => $this->contentItem?->id
                ),


                'title' => $this->whenLoaded(
                    'contentItem',
                    fn () => $this->contentItem?->title
                ),


                'page_number' => $this->whenLoaded(
                    'contentItem',
                    fn () => $this->contentItem?->page_number
                ),


                'section' => $this->whenLoaded(
                    'contentItem',
                    fn () => $this->contentItem?->section
                        ? [

                            'id' =>
                                $this->contentItem
                                    ->section
                                    ->id,


                            'title' =>
                                $this->contentItem
                                    ->section
                                    ->title,


                            'chapter' => [

                                'id' =>
                                    $this->contentItem
                                        ->section
                                        ->chapter
                                        ?->id,


                                'title' =>
                                    $this->contentItem
                                        ->section
                                        ->chapter
                                        ?->title,


                                'book' => [

                                    'id' =>
                                        $this->contentItem
                                            ->section
                                            ->chapter
                                            ?->book
                                            ?->id,


                                    'title' =>
                                        $this->contentItem
                                            ->section
                                            ->chapter
                                            ?->book
                                            ?->title,

                                ],

                            ],

                        ]
                        : null
                ),

            ],



            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */


            'uploader' => [

                'id' => $this->whenLoaded(
                    'uploader',
                    fn () => $this->uploader?->id
                ),


                'name' => $this->whenLoaded(
                    'uploader',
                    fn () => $this->uploader?->name
                ),

            ],



            'approver' => [

                'id' => $this->whenLoaded(
                    'approver',
                    fn () => $this->approver?->id
                ),


                'name' => $this->whenLoaded(
                    'approver',
                    fn () => $this->approver?->name
                ),

            ],



            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */


            'directory' => $this->directory,


            'filename' => $this->filename,


            'video_url' => $this->directory && $this->filename

                ? asset(
                    $this->directory . '/' . $this->filename
                )

                : null,



            'original_name' => $this->original_name,


            'extension' => $this->extension,


            'mime_type' => $this->mime_type,


            'file_size' => $this->file_size,



            /*
            |--------------------------------------------------------------------------
            | Video Information
            |--------------------------------------------------------------------------
            */


            'duration' => $this->duration,


            'quality' => $this->quality,


            'thumbnail_url' => $this->thumbnail_path

                ? asset($this->thumbnail_path)

                : null,



            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */


            'views_count' => $this->views_count,


            'download_allowed' => $this->download_allowed,



            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */


            'processing_status' => $this->processing_status,


            'approved_at' => $this->approved_at,


            'rejected_reason' => $this->rejected_reason,



            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */


            'created_at' => $this->created_at,


            'updated_at' => $this->updated_at,

        ];
    }
}
