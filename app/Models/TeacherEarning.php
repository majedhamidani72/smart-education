<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherEarning extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | فیلدهای قابل ثبت
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'teacher_id',

        'purchase_id',

        'purchase_item_id',

        'sale_amount',

        'percentage',

        'amount',

        'status',

        'settlement_number',

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

            'sale_amount' => 'integer',

            'amount' => 'integer',

            'percentage' => 'integer',

            'paid_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | روابط
    |--------------------------------------------------------------------------
    */

    /**
     * معلم
     */
    public function teacher()
    {
        return $this->belongsTo(
            User::class,
            'teacher_id'
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
     * آیتم خرید
     */
    public function purchaseItem()
    {
        return $this->belongsTo(
            PurchaseItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * آیا تسویه شده است؟
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * در انتظار تسویه
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * لغو شده
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * ثبت تسویه
     */
    public function markAsPaid(
        string $settlementNumber
    ): void
    {
        $this->update([

            'status' => 'paid',

            'paid_at' => now(),

            'settlement_number' => $settlementNumber,

        ]);
    }
}
