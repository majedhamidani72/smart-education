<?php

namespace App\Models;

use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'app_grade_subject_id',

        'title',

        'slug',

        'cover',

        'is_active',

        'sort_order',

    ];

    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * اپ + پایه + درس
     */
    public function appGradeSubject(): BelongsTo
    {
        return $this->belongsTo(
            AppGradeSubject::class
        );
    }

    /**
     * فصل‌های کتاب
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(
            Chapter::class
        );
    }

    /**
     * معلمان مجاز کتاب
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(
            TeacherAssignment::class
        );
    }
}
