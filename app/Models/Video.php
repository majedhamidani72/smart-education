<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Video extends Model
{
    use HasFactory;



    // فیلدهای قابل ذخیره
    protected $fillable = [

        'content_item_id',
        // محتوای آموزشی مربوطه


        'storage_disk',
        // محل ذخیره فایل


        'file_path',
        // مسیر فایل ویدئو


        'original_name',
        // نام اصلی فایل


        'mime_type',
        // نوع فایل


        'file_size',
        // حجم فایل به بایت


        'duration',
        // مدت زمان ویدئو به ثانیه


        'quality',
        // کیفیت ویدئو مثل 720p


        'thumbnail_path',
        // مسیر تصویر بندانگشتی


        'views_count',
        // تعداد بازدید


        'download_allowed',
        // اجازه دانلود

    ];



    // تبدیل نوع داده‌ها
    protected function casts(): array
    {
        return [

            'download_allowed' => 'boolean',
            // تبدیل اجازه دانلود به true/false


            'views_count' => 'integer',
            // تعداد بازدید عددی

        ];
    }



    // =========================
    // Relationships
    // =========================


    // هر ویدئو متعلق به یک محتوای آموزشی است
    public function contentItem()
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

}
