<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'title',

        'slug',

        'exam_structure',

        'description',

        'icon',

        'sort_order',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }



    // یک درس متعلق به چند پایه است
    public function grades()
    {
        return $this->belongsToMany(
            Grade::class,
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
