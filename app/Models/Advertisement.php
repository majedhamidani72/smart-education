<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'title',

        'image',

        'link',

        'description',

        'position',

        'sort_order',

        'is_active',

        'starts_at',

        'expires_at',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

            'is_active' => 'boolean',

            'sort_order' => 'integer',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * بازدیدها
     */
    public function views()
    {
        return $this->hasMany(
            AdvertisementView::class
        );
    }

    /**
     * کلیک‌ها
     */
    public function clicks()
    {
        return $this->hasMany(
            AdvertisementClick::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * فعال بودن تبلیغ
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {

            return false;

        }

        if (

            $this->starts_at

            &&

            now()->lt($this->starts_at)

        ) {

            return false;

        }

        if (

            $this->expires_at

            &&

            now()->gt($this->expires_at)

        ) {

            return false;

        }

        return true;
    }

    /**
     * تعداد بازدید
     */
    public function totalViews(): int
    {
        return $this->views()->count();
    }

    /**
     * تعداد کلیک
     */
    public function totalClicks(): int
    {
        return $this->clicks()->count();
    }

    /**
     * نرخ کلیک
     */
    public function ctr(): float
    {
        $views = $this->totalViews();

        if ($views === 0) {

            return 0;

        }

        return round(

            ($this->totalClicks() / $views) * 100,

            2

        );
    }
}
