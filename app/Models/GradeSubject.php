<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GradeSubject extends Model
{
    use HasFactory;

    protected $table = 'grade_subject';

    protected $fillable = [
        'grade_id',
        'subject_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * پایه
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * درس
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * کتاب‌های این پایه و درس
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
