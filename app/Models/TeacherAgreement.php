<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAgreement extends Model
{
    use HasFactory;


    protected $fillable = [

        'teacher_id',
        // معلم


        'agreement_version',
        // نسخه قوانین


        'accepted_at',
        // زمان تایید


        'ip_address',
        // آی‌پی


        'user_agent',
        // اطلاعات دستگاه

    ];



    protected function casts(): array
    {
        return [

            'accepted_at' => 'datetime',

        ];
    }



    // هر رضایتنامه مربوط به یک معلم است
    public function teacher()
    {
        return $this->belongsTo(
            User::class,
            'teacher_id'
        );
    }

}
