<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAttempt extends Model
{
    use HasFactory;


    protected $fillable = [

        'quiz_attempt_id',

        'question_id',

        'question_option_id',

        'is_correct',

        'score_awarded',

        'question_snapshot',

        'options_snapshot',

        'answered_at',

    ];



    protected function casts(): array
    {
        return [

            'is_correct' => 'boolean',

            'score_awarded' => 'integer',

            'question_snapshot' => 'array',

            'options_snapshot' => 'array',

            'answered_at' => 'datetime',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * مربوط به یک تلاش آزمون
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(
            QuizAttempt::class
        );
    }



    /**
     * سوال مربوطه
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            Question::class
        );
    }



    /**
     * گزینه انتخاب شده توسط دانش آموز
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(
            QuestionOption::class,
            'question_option_id'
        );
    }

}
