<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    /**
     * فیلدهای قابل ذخیره
     */
    protected $fillable = [

        'user_id',

        'mobile',

        'code',

        'login_token',

        'purpose',

        'attempts',

        'is_verified',

        'expires_at',

        'verified_at',

        'used_at',

        'ip_address',

        'user_agent',

    ];

    /**
     * تبدیل نوع داده‌ها
     */
    protected function casts(): array
    {
        return [

            'is_verified' => 'boolean',

            'expires_at' => 'datetime',

            'verified_at' => 'datetime',

            'used_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * کاربر مربوطه
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * OTP های فعال
     */
    public function scopeActive($query)
    {
        return $query

            ->whereNull('used_at')

            ->where('expires_at', '>', now());

    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * بررسی انقضای OTP
     */
    public function isExpired(): bool
    {
        return now()->greaterThan(

            $this->expires_at

        );
    }

    /**
     * بررسی استفاده شدن OTP
     */
    public function isUsed(): bool
    {
        return !is_null(

            $this->used_at

        );
    }

    /**
     * افزایش تعداد تلاش
     */
    public function incrementAttempts(): void
    {
        $this->increment(

            'attempts'

        );
    }

    /**
     * ثبت تایید OTP
     */
    public function markAsVerified(): void
    {
        $this->update([

            'is_verified' => true,

            'verified_at' => now(),

            'used_at' => now(),

        ]);
    }

    /**
     * غیرفعال کردن OTP
     */
    public function markAsUsed(): void
    {
        $this->update([

            'used_at' => now(),

        ]);
    }
}
