<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'title',

        'description',

        'planable_type',

        'planable_id',

        'price',

        'discount_price',

        'purchase_type',

        'duration_days',

        'is_active',

        'sort_order',

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

            'price' => 'integer',

            'discount_price' => 'integer',

            'duration_days' => 'integer',

            'is_active' => 'boolean',

            'sort_order' => 'integer',

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * محصول مربوط به پلن
     */
    public function planable()
    {
        return $this->morphTo();
    }

    /**
     * آیتم‌های خرید
     */
    public function purchaseItems()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }

    /**
     * اشتراک‌ها
     */
    public function subscriptions()
    {
        return $this->hasMany(
            Subscription::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * آیا فعال است؟
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
     * آیا اشتراکی است؟
     */
    public function isSubscription(): bool
    {
        return $this->purchase_type === 'subscription';
    }

    /**
     * آیا خرید دائمی است؟
     */
    public function isOneTime(): bool
    {
        return $this->purchase_type === 'one_time';
    }

    /**
     * قیمت نهایی
     */
    public function finalPrice(): int
    {
        if (

            $this->discount_price

            &&

            $this->discount_price > 0

        ) {

            return $this->discount_price;

        }

        return $this->price;
    }

    /**
     * میزان تخفیف
     */
    public function discountAmount(): int
    {
        return max(

            0,

            $this->price - $this->finalPrice()

        );
    }

    /**
     * درصد تخفیف
     */
    public function discountPercent(): int
    {
        if ($this->price <= 0) {

            return 0;

        }

        return (int) round(

            ($this->discountAmount() / $this->price) * 100

        );
    }
}
