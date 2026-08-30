<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'title',

        'slug',

        'grade_number',

        'sort_order',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }





    // ارتباط با اپلیکیشن‌ها
    public function appGradeSubjects()
    {
        return $this->hasMany(
            AppGradeSubject::class
        );
    }
}
