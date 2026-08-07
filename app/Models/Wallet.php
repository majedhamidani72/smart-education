<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'balance',

        'is_active',

        'last_transaction_at',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'balance' => 'integer',

            'is_active' => 'boolean',

            'last_transaction_at' => 'datetime',

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
     * تراکنش‌های کیف پول
     */
    public function transactions()
    {
        return $this->hasMany(
            WalletTransaction::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * افزایش موجودی
     */
    public function deposit(
        int $amount
    ): void
    {
        $this->increment(
            'balance',
            $amount
        );

        $this->update([

            'last_transaction_at' => now(),

        ]);
    }

    /**
     * کاهش موجودی
     */
    public function withdraw(
        int $amount
    ): bool
    {
        if ($this->balance < $amount) {

            return false;

        }

        $this->decrement(
            'balance',
            $amount
        );

        $this->update([

            'last_transaction_at' => now(),

        ]);

        return true;
    }

    /**
     * بررسی موجودی
     */
    public function hasEnoughBalance(
        int $amount
    ): bool
    {
        return $this->balance >= $amount;
    }
}
