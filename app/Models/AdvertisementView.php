<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisementView extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'advertisement_id',

        'user_id',

        'ip_address',

        'user_agent',

        'device_type',

        'platform',

    ];

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * تبلیغ
     */
    public function advertisement()
    {
        return $this->belongsTo(
            Advertisement::class
        );
    }

    /**
     * کاربر
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * بازدید مهمان
     */
    public function isGuest(): bool
    {
        return is_null(
            $this->user_id
        );
    }

    /**
     * بازدید کاربر وارد شده
     */
    public function isAuthenticated(): bool
    {
        return !is_null(
            $this->user_id
        );
    }
}
