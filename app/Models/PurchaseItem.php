<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'purchase_id',

        'plan_id',

        'item_type',

        'item_id',

        'title',

        'price',

        'discount_amount',

        'final_price',

        'quantity',

        'notes',

    ];

    /*
    |--------------------------------------------------------------------------
    | تبدیل نوع داده‌ها
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'price' => 'integer',

            'discount_amount' => 'integer',

            'final_price' => 'integer',

            'quantity' => 'integer',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

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
     * پلن خریداری شده
     */
    public function plan()
    {
        return $this->belongsTo(
            Plan::class
        );
    }

    /**
     * اشتراک ایجاد شده
     */
    public function subscription()
    {
        return $this->hasOne(
            Subscription::class,
            'purchase_id',
            'purchase_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * کتاب
     */
    public function isBook(): bool
    {
        return $this->item_type === 'book';
    }

    /**
     * درس
     */
    public function isLesson(): bool
    {
        return $this->item_type === 'lesson';
    }

    /**
     * اشتراک
     */
    public function isSubscription(): bool
    {
        return $this->item_type === 'subscription';
    }

    /**
     * بسته آموزشی
     */
    public function isPackage(): bool
    {
        return $this->item_type === 'package';
    }

    /**
     * آزمون
     */
    public function isQuiz(): bool
    {
        return $this->item_type === 'quiz';
    }

    public function isPowerpoint(): bool
    {
        return $this->item_type === 'powerpoint';
    }

    /**
     * مبلغ کل
     */
    public function getTotalPrice(): int
    {
        return (int) (

            $this->final_price

            *

            $this->quantity

        );
    }

    /**
     * مبلغ تخفیف
     */
    public function getDiscount(): int
    {
        return (int) $this->discount_amount;
    }

    /**
     * قیمت اصلی
     */
    public function getPrice(): int
    {
        return (int) $this->price;
    }

    /**
     * قیمت نهایی
     */
    public function getFinalPrice(): int
    {
        return (int) $this->final_price;
    }
}
