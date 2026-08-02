<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentType extends Model
{
    use HasFactory, SoftDeletes;



    // فیلدهای قابل ذخیره
    protected $fillable = [

        'title',
        // نام نوع محتوا
        // video
        // pdf
        // step_by_step
        // sample_question


        'slug',
        // نام یکتا


        'icon',
        // آیکون نوع محتوا


        'sort_order',
        // ترتیب نمایش


        'is_active',
        // فعال بودن

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }



    // محتواهای مربوط به این نوع
    public function contentItems()
    {
        return $this->hasMany(
            ContentItem::class
        );
    }

}
