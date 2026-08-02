<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;


    // فیلدهای قابل ذخیره
    protected $fillable = [

        'key', // نام تنظیم

        'value', // مقدار تنظیم

    ];

}
