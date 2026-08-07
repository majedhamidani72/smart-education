<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'purchase_id',

        'plan_id',

        'status',

        'starts_at',

        'expires_at',

        'cancelled_at',

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

            'cancelled_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * کاربر
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * خرید
     */
    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }

    /**
     * پلن
     */
    public function plan()
    {
        return $this->belongsTo(
            Plan::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * فعال است؟
     */
    public function isActive(): bool
    {
        return

            $this->status === 'active'

            &&

            now()->lessThanOrEqualTo(
                $this->expires_at
            );
    }

    /**
     * منقضی شده؟
     */
    public function isExpired(): bool
    {
        return

            now()->greaterThan(
                $this->expires_at
            );
    }

    /**
     * لغو شده؟
     */
    public function isCancelled(): bool
    {
        return

            $this->status === 'cancelled';
    }

    /**
     * تعداد روزهای باقی‌مانده
     */
    public function remainingDays(): int
    {
        if (

            $this->isExpired()

        ) {

            return 0;

        }

        return

            now()->diffInDays(
                $this->expires_at
            );
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        int $days
    ): void
    {
        $this->update([

            'expires_at' => $this->expires_at
                ->copy()
                ->addDays($days),

        ]);
    }

    /**
     * لغو اشتراک
     */
    public function cancel(): void
    {
        $this->update([

            'status' => 'cancelled',

            'cancelled_at' => now(),

        ]);
    }

    /**
     * فعال‌سازی مجدد
     */
    public function activate(): void
    {
        $this->update([

            'status' => 'active',

            'cancelled_at' => null,

        ]);
    }

    /**
     * پایان یافته ولی وضعیت هنوز Active است؟
     */
    public function needsExpiration(): bool
    {
        return

            $this->status === 'active'

            &&

            now()->greaterThan(
                $this->expires_at
            );
    }

    /**
     * منقضی کردن اشتراک
     */
    public function markAsExpired(): void
    {
        $this->update([

            'status' => 'expired',

        ]);
    }
}
