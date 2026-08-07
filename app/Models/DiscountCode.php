<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'code',

        'title',

        'description',

        'type',

        'value',

        'max_discount',

        'minimum_purchase',

        'usage_limit',

        'used_count',

        'usage_per_user',

        'starts_at',

        'expires_at',

        'is_active',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'value' => 'integer',

            'max_discount' => 'integer',

            'minimum_purchase' => 'integer',

            'usage_limit' => 'integer',

            'used_count' => 'integer',

            'usage_per_user' => 'integer',

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

            'is_active' => 'boolean',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * فعال بودن
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * منقضی شده؟
     */
    public function isExpired(): bool
    {
        return $this->expires_at
            && now()->greaterThan($this->expires_at);
    }

    /**
     * شروع شده؟
     */
    public function hasStarted(): bool
    {
        return !$this->starts_at
            || now()->greaterThanOrEqualTo($this->starts_at);
    }

    /**
     * ظرفیت تمام شده؟
     */
    public function isLimitReached(): bool
    {
        if (!$this->usage_limit) {

            return false;

        }

        return $this->used_count >= $this->usage_limit;
    }

    /**
     * معتبر است؟
     */
    public function isValid(): bool
    {
        return $this->isActive()

            &&

            $this->hasStarted()

            &&

            !$this->isExpired()

            &&

            !$this->isLimitReached();
    }

    /**
     * افزایش تعداد استفاده
     */
    public function increaseUsage(): void
    {
        $this->increment(
            'used_count'
        );
    }
}
