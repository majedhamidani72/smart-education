<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;


    protected $fillable = [

        'user_id',
        // کاربر دریافت کننده

        'title',
        // عنوان

        'message',
        // متن پیام

        'is_read',
        // وضعیت خواندن

    ];


    protected function casts(): array
    {
        return [

            'is_read' => 'boolean',

        ];
    }



    // هر اعلان متعلق به یک کاربر است
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
