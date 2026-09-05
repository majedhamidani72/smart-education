<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | لاگ فعالیت
    |--------------------------------------------------------------------------
    | هر خرید (و هر تغییری روی آن، مثل تغییر وضعیت یا بازگشت وجه) به‌طور
    | خودکار و دائمی (توی جدول activity_log) با مشخصِ کردنِ اینکه چه کسی
    | چه زمانی چه کاری کرده، ثبت می‌شود — مستقل از فایل‌های لاگ متنی که
    | ممکن است پاک یا Rotate شوند. این برای پاسخ به شکایت‌ها یا اختلافات
    | احتمالی کاربران، یک مدرک قابل‌استناد و قابل‌جستجوست.
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('purchases');
    }

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'invoice_number',

        'total_amount',

        'discount_amount',

        'payable_amount',

        'status',

        'paid_at',

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

            'total_amount' => 'integer',

            'discount_amount' => 'integer',

            'payable_amount' => 'integer',

            'paid_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * خریدار
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * آیتم‌های خرید
     */
    public function items()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }

    /**
     * تراکنش‌های پرداخت
     */
    public function paymentTransactions()
    {
        return $this->hasMany(
            PaymentTransaction::class
        );
    }

    /**
     * آخرین تراکنش پرداخت
     */
    public function latestTransaction()
    {
        return $this->hasOne(
            PaymentTransaction::class
        )->latestOfMany();
    }

    /**
     * درآمد معلمان
     */
    public function teacherEarnings()
    {
        return $this->hasMany(
            TeacherEarning::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * پرداخت شده؟
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * در انتظار پرداخت؟
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * لغو شده؟
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * برگشت وجه؟
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * مبلغ کل
     */
    public function getTotalAmount(): int
    {
        return (int) $this->total_amount;
    }

    /**
     * مبلغ تخفیف
     */
    public function getDiscountAmount(): int
    {
        return (int) $this->discount_amount;
    }

    /**
     * مبلغ قابل پرداخت
     */
    public function getPayableAmount(): int
    {
        return (int) $this->payable_amount;
    }

    /**
     * تعداد آیتم‌های خرید
     */
    public function itemsCount(): int
    {
        return $this->items()->count();
    }
}
