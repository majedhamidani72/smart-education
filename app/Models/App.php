<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
    use HasFactory, SoftDeletes;



    // فیلدهای قابل ذخیره
    protected $fillable = [

        'title',
        // عنوان اپلیکیشن


        'slug',
        // نام یکتا


        'description',
        // توضیحات


        'package_name',
        // نام پکیج اندروید


        'icon',
        // آیکون اپ


        'current_version',
        // نسخه فعلی


        'force_update',
        // اجبار بروزرسانی


        'is_active',
        // فعال بودن


        'sort_order',
        // ترتیب نمایش

    ];



    protected function casts(): array
    {
        return [

            'force_update' => 'boolean',

            'is_active' => 'boolean',

        ];
    }

}
