<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AdvertisementView extends Model
{
    use HasFactory;



    // فیلدهایی که اجازه ذخیره دارند
    protected $fillable = [

        'advertisement_id',
        // تبلیغ مشاهده شده


        'user_id',
        // کاربر مشاهده کننده (ممکن است مهمان باشد)

    ];



    // =========================
    // Relationships
    // =========================


    // هر بازدید مربوط به یک تبلیغ است
    public function advertisement()
    {
        return $this->belongsTo(
            Advertisement::class
        );
    }



    // هر بازدید ممکن است توسط یک کاربر باشد
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

}
