<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

            'correct_answers_count' => 'integer',

            'wrong_answers_count' => 'integer',

            'unanswered_count' => 'integer',

            'duration_seconds' => 'integer',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * دانش آموز
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }



    /**
     * آزمون
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(
            Quiz::class
        );
    }



    /**
     * پاسخ های ثبت شده سوالات
     */
    public function questionAttempts(): HasMany
    {
        return $this->hasMany(
            QuestionAttempt::class
        );
    }
}
