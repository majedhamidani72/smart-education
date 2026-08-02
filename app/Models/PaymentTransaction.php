<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;



    protected $fillable = [

        'purchase_id',

        'user_id',

        'gateway',

        'transaction_id',

        'reference_id',

        'amount',

        'status',

        'message',

        'gateway_response',

        'paid_at',

    ];



    protected function casts(): array
    {
        return [

            'gateway_response' => 'array',

            'paid_at' => 'datetime',

        ];
    }



    // هر تراکنش متعلق به یک خرید است
    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }



    // کاربر پرداخت کننده
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

}
