<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;



    // فیلدهایی که اجازه ذخیره دارند
    protected $fillable = [

        'title',
        // عنوان پلن


        'description',
        // توضیحات پلن


        'planable_type',
        // نوع محصول قابل فروش
        // مثال: App, Book, Subject


        'planable_id',
        // شناسه محصول قابل فروش


        'price',
        // قیمت اصلی


        'discount_price',
        // قیمت با تخفیف


        'purchase_type',
        // نوع خرید
        // one_time
        // subscription


        'duration_days',
        // مدت اشتراک به روز


        'is_active',
        // فعال بودن پلن


        'sort_order',
        // ترتیب نمایش


        'starts_at',
        // شروع فروش


        'expires_at',
        // پایان فروش

    ];



    protected function casts(): array
    {
        return [

            'price' => 'integer',

            'discount_price' => 'integer',

            'is_active' => 'boolean',

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

        ];
    }



    // محصولی که این پلن برای آن ساخته شده
    public function planable()
    {
        return $this->morphTo();
    }



    // آیتم‌های خرید مربوط به این پلن
    public function purchaseItems()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }

}
