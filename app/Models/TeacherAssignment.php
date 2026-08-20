<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\BelongsTo;



class TeacherAssignment extends Model
{

    use HasFactory;

    use SoftDeletes;



    protected $fillable = [

        'teacher_id',

        'book_id',

        'commission_percentage_zibal',

        'commission_percentage_bazaar',

        'commission_percentage_myket',

        'assigned_by',

        'is_active',

    ];




    protected function casts(): array
    {

        return [

            'is_active' => 'boolean',

        ];

    }





    /*
    |--------------------------------------------------------------------------
    | Teacher
    |--------------------------------------------------------------------------
    |
    | معلمی که این کتاب به او اختصاص داده شده
    |
    */


    public function teacher(): BelongsTo
    {

        return $this->belongsTo(

            User::class,

            'teacher_id'

        );

    }





    /*
    |--------------------------------------------------------------------------
    | Book
    |--------------------------------------------------------------------------
    |
    | کتابی که معلم اجازه مدیریت آن را دارد
    |
    */


    /**
     * درصد سهم معلم را متناسب با درگاهی که فروش از آن آمده
     * برمی‌گرداند (zibal، bazaar، یا myket). اگر نام درگاه ناشناخته
     * بود، برای احتیاط از پایین‌ترین/پیش‌فرض زیبال استفاده می‌شود.
     */
    public function commissionPercentageFor(string $gateway): int
    {
        return match ($gateway) {

            'bazaar' => $this->commission_percentage_bazaar,

            'myket' => $this->commission_percentage_myket,

            default => $this->commission_percentage_zibal,
        };
    }

    public function book(): BelongsTo
    {

        return $this->belongsTo(

            Book::class

        );

    }





    /*
    |--------------------------------------------------------------------------
    | Assigned By
    |--------------------------------------------------------------------------
    |
    | ادمین یا سوپرادمینی که این دسترسی را ایجاد کرده
    |
    */


    public function assignedBy(): BelongsTo
    {

        return $this->belongsTo(

            User::class,

            'assigned_by'

        );

    }



}
