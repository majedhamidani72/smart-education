<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    /**
     * فیلدهای قابل ذخیره
     */
    protected $fillable = [

        'user_id',

        'device_identifier',

        'device_name',

        'manufacturer',

        'model',

        'platform',

        'os_version',

        'app_version',

        'fcm_token',

        'last_ip',

        'last_login_at',

        'is_active',

    ];

    /**
     * تبدیل نوع داده‌ها
     */
    protected function casts(): array
    {
        return [

            'last_login_at' => 'datetime',

            'is_active' => 'boolean',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * هر دستگاه متعلق به یک کاربر است
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
     * فقط دستگاه‌های فعال
     */
    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    /**
     * فقط دستگاه‌های اندروید
     */
    public function scopeAndroid($query)
    {
        return $query->where(
            'platform',
            'android'
        );
    }

    /**
     * فقط دستگاه‌های iOS
     */
    public function scopeIos($query)
    {
        return $query->where(
            'platform',
            'ios'
        );
    }

    /**
     * فقط دستگاه‌های وب
     */
    public function scopeWeb($query)
    {
        return $query->where(
            'platform',
            'web'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * فعال کردن دستگاه
     */
    public function activate(): void
    {
        $this->update([

            'is_active' => true,

            'last_login_at' => now(),

        ]);
    }

    /**
     * غیرفعال کردن دستگاه
     */
    public function deactivate(): void
    {
        $this->update([

            'is_active' => false,

        ]);
    }

    /**
     * بروزرسانی FCM Token
     */
    public function updateFcmToken(
        ?string $token
    ): void {

        $this->update([

            'fcm_token' => $token,

        ]);

    }

    /**
     * بروزرسانی آخرین ورود
     */
    public function updateLastLogin(
        ?string $ip
    ): void {

        $this->update([

            'last_login_at' => now(),

            'last_ip' => $ip,

        ]);

    }
}
