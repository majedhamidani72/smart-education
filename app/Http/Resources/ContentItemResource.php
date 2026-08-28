<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * خروجی محتوای آموزشی برای دانش‌آموز
 * --------------------------------------------------------------------
 * منطق دسترسی: اگر محتوا رایگان است (is_free)، همه می‌بینند —
 * حتی بدون ورود. اگر رایگان نیست، فقط کاربری که وارد شده و
 * دسترسی خریداری‌شده دارد (User::hasAccessToContentItem)، فایل
 * واقعی را می‌بیند؛ در غیر این صورت فقط عنوان/توضیحات برمی‌گردد
 * و has_access=false است — تا کلاینت بداند باید پیشنهاد خرید
 * نشان دهد، نه این‌که خطا بگیرد.
 */
class ContentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user('sanctum');

        $hasAccess = (bool) $this->is_free
            || ($user && $user->hasAccessToContentItem($this->resource));

        return [

            'id' => $this->id,

            'chapter_id' => $this->chapter_id,

            'section_id' => $this->section_id,

            'content_type_id' => $this->content_type_id,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'page_number' => $this->page_number,

            'thumbnail' => $this->thumbnail,

            'is_free' => $this->is_free,

            'has_access' => $hasAccess,

            'sort_order' => $this->sort_order,

            'published_at' => $this->published_at,

            'section' => $this->whenLoaded('section'),

            'content_type' => $this->whenLoaded('contentType'),

            /*
            |--------------------------------------------------------------------------
            | فایل واقعی — فقط وقتی دسترسی واقعاً وجود دارد
            |--------------------------------------------------------------------------
            */

            'video' => $hasAccess
                ? $this->whenLoaded('video', fn () => new VideoResource($this->video))
                : null,

            'pdf_file' => $hasAccess
                ? $this->whenLoaded('pdfFile', fn () => new PdfFileResource($this->pdfFile))
                : null,

            'step_by_step' => $hasAccess
                ? $this->whenLoaded('stepByStep', fn () => $this->stepByStep ? [
                    'id' => $this->stepByStep->id,
                    'pages' => StepByStepPageResource::collection($this->stepByStep->pages ?? collect()),
                ] : null)
                : null,

            'sample_questions' => $hasAccess
                ? $this->whenLoaded(
                    'sampleQuestions',
                    fn () => SampleQuestionResource::collection(
                        $this->sampleQuestions->where('processing_status', 'approved')
                    )
                )
                : null,

        ];
    }
}
