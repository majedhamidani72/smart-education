<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEarning extends Model
{
    use HasFactory;


    protected $fillable = [

        'teacher_id', // معلم

        'purchase_id', // خرید مربوطه

        'amount', // مبلغ سهم معلم

        'percentage', // درصد سهم

        'status', // pending یا paid

        'paid_at', // زمان تسویه

    ];



    protected function casts(): array
    {
        return [

            'paid_at' => 'datetime',

        ];
    }



    // =========================
    // Relationships
    // =========================


    // درآمد متعلق به یک معلم است
    public function teacher()
    {
        return $this->belongsTo(
            User::class,
            'teacher_id'
        );
    }



    // مربوط به یک خرید است
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

}
