<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'grade_subject_id',

        'title',

        'slug',

        'cover',

        'academic_year',

        'pages_count',

        'description',

        'is_active',

        'sort_order',

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }



    // پایه و درس مربوط به کتاب
    public function gradeSubject()
    {
        return $this->belongsTo(
            GradeSubject::class
        );
    }



    // فصل‌های کتاب
    public function chapters()
    {
        return $this->hasMany(
            Chapter::class
        );
    }

}
