<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'wallet_id',

        'user_id',

        'type',

        'amount',

        'balance_before',

        'balance_after',

        'status',

        'description',

        'meta',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'amount' => 'integer',

            'balance_before' => 'integer',

            'balance_after' => 'integer',

            'meta' => 'array',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * کیف پول
     */
    public function wallet()
    {
        return $this->belongsTo(
            Wallet::class
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
     * تراکنش موفق
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * تراکنش ناموفق
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * تراکنش واریز
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * تراکنش برداشت
     */
    public function isWithdraw(): bool
    {
        return $this->type === 'withdraw';
    }

    /**
     * تراکنش خرید
     */
    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

    /**
     * بازگشت وجه
     */
    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }
}
