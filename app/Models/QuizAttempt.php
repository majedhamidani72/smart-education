<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;



    protected $fillable = [

        'user_id',

        'quiz_id',

        'total_score',

        'earned_score',

        'percentage',

        'correct_answers_count',

        'wrong_answers_count',

        'unanswered_count',

        'status',

        'started_at',

        'finished_at',

        'duration_seconds',

    ];



    protected function casts(): array
    {
        return [

            'started_at' => 'datetime',

            'finished_at' => 'datetime',

            'percentage' => 'decimal:2',

            'total_score' => 'integer',

            'earned_score' => 'integer',

            'duration_seconds' => 'integer',

        ];
    }



    // کاربر شرکت کننده
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }



    // آزمون
    public function quiz()
    {
        return $this->belongsTo(
            Quiz::class
        );
    }



    // پاسخ‌های سوالات
    public function questionAttempts()
    {
        return $this->hasMany(
            QuestionAttempt::class
        );
    }
}
