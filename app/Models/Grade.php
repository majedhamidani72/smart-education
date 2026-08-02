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



    // یک پایه شامل چند درس است
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'grade_subject'
        );
    }



    // ارتباط با اپلیکیشن‌ها
    public function appGradeSubjects()
    {
        return $this->hasMany(
            AppGradeSubject::class
        );
    }

}
