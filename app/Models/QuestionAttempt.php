<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

            'question_snapshot' => 'array',

            'options_snapshot' => 'array',

            'answered_at' => 'datetime',

        ];
    }



    // مربوط به یک آزمون انجام شده
    public function quizAttempt()
    {
        return $this->belongsTo(
            QuizAttempt::class
        );
    }



    // مربوط به یک سوال
    public function question()
    {
        return $this->belongsTo(
            Question::class
        );
    }



    // گزینه انتخاب شده
    public function selectedOption()
    {
        return $this->belongsTo(
            QuestionOption::class,
            'question_option_id'
        );
    }

}
