<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;


    protected $fillable = [

        'title', // عنوان تبلیغ

        'image', // تصویر تبلیغ

        'link', // لینک مقصد

        'position', // محل نمایش

        'start_date', // شروع نمایش

        'end_date', // پایان نمایش

        'is_active', // فعال بودن

    ];



    protected function casts(): array
    {
        return [

            'start_date' => 'datetime',

            'end_date' => 'datetime',

            'is_active' => 'boolean',

        ];
    }


    // بررسی فعال بودن تبلیغ
    public function isActive(): bool
    {
        return $this->is_active
            && now()->between(
                $this->start_date,
                $this->end_date
            );
    }

}
