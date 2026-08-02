<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;


    // فیلدهای قابل ذخیره
    protected $fillable = [

        'user_id',
        // کاربر صاحب اشتراک


        'purchase_item_id',
        // آیتم خرید مربوط به اشتراک


        'starts_at',
        // زمان شروع


        'expires_at',
        // زمان پایان


        'status',
        // وضعیت اشتراک

    ];



    // تبدیل نوع داده‌ها
    protected function casts(): array
    {
        return [

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

        ];
    }



    // =========================
    // Relationships
    // =========================


    // هر اشتراک متعلق به یک کاربر است
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    // هر اشتراک از یک آیتم خرید ساخته شده است
    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }



    // بررسی فعال بودن اشتراک
    public function isActive(): bool
    {
        return $this->status === 'active'
            && now()->lessThan($this->expires_at);
    }

}
