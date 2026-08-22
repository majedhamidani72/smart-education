<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * خروجی معلم برای دانش‌آموز — طبق تصمیم پروژه، عمداً حداقلی است
 * (فقط شناسه و نام)؛ بدون عکس، امتیاز، یا نظرات — تا فاز اول
 * اپلیکیشن ساده و سبک بماند.
 */
class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

        ];
    }
}
