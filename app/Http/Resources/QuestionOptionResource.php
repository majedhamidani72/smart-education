<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'option_text' => $this->option_text,

            'image_path' => $this->image_path
                ? Storage::url($this->image_path)
                : null,

            /*
            |----------------------------------------------------------------------
            | فقط در شرایط مجاز نمایش داده می‌شود
            | مثال:
            | - پنل مدیریت
            | - نمایش نتیجه آزمون
            |----------------------------------------------------------------------
            */

            'is_correct' => $this->when(
                $request->user()?->hasAnyRole(['SuperAdmin', 'Admin', 'Teacher']),
                $this->is_correct
            ),


            // فقط اگر این گزینه غلط باشد و پیشنهادی برایش تعریف
            // شده باشد، برای گزارش پایان آزمون (وقتی دانش‌آموز این
            // گزینه را زده) برگردانده می‌شود.
            'recommendation_text' => $this->when(
                ! $this->is_correct && filled($this->recommendation_text),
                $this->recommendation_text
            ),


            'sort_order' => $this->sort_order,


            'created_at' => $this->created_at
                ? $this->created_at->format('Y-m-d H:i:s')
                : null,


            'updated_at' => $this->updated_at
                ? $this->updated_at->format('Y-m-d H:i:s')
                : null,

        ];
    }
}
