<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'user_id',

        'invoice_number',

        'total_amount',

        'discount_amount',

        'payable_amount',

        'payment_status',

        'payment_method',

        'paid_at',

    ];



    protected function casts(): array
    {
        return [

            'paid_at' => 'datetime',

        ];
    }



    // خرید متعلق به کاربر
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }



    // آیتم‌های خرید
    public function items()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }



    // تراکنش پرداخت
    public function paymentTransaction()
    {
        return $this->hasOne(
            PaymentTransaction::class
        );
    }



    // اشتراک مربوط به خرید
    public function subscription()
    {
        return $this->hasOne(
            Subscription::class
        );
    }

}
