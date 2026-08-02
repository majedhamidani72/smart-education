<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;



    protected $fillable = [

        'purchase_id',
        // خرید اصلی


        'plan_id',
        // پلن خریداری شده


        'price',
        // قیمت اصلی


        'discount_amount',
        // تخفیف


        'final_price',
        // مبلغ نهایی

    ];



    // =========================
    // Relationships
    // =========================


    // هر آیتم متعلق به یک خرید است
    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }



    // هر آیتم مربوط به یک پلن است
    public function plan()
    {
        return $this->belongsTo(
            Plan::class
        );
    }



    // اشتراک ساخته شده از این آیتم
    public function subscription()
    {
        return $this->hasOne(
            Subscription::class
        );
    }

}
