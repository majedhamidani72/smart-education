<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;


    protected $fillable = [

        'user_id',
        // کاربر انجام دهنده عملیات


        'action',
        // نوع عملیات


        'description',
        // توضیحات

    ];



    // هر لاگ مربوط به یک کاربر است
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
