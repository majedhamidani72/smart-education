<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [

        'quizable_type',

        'quizable_id',

        'created_by',

        'reviewed_by',

        'title',

        'description',

        'questions_count',

        'time_limit',

        'passing_percentage',

        'max_attempts',

        'randomize_questions',

        'randomize_options',

        'show_result',

        'show_correct_answers',

        'is_free',

        'status',

        'rejection_reason',

        'published_at',

    ];



    protected function casts(): array
    {
        return [

            'randomize_questions' => 'boolean',

            'randomize_options' => 'boolean',

            'show_result' => 'boolean',

            'show_correct_answers' => 'boolean',

            'is_free' => 'boolean',

            'published_at' => 'datetime',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * ارتباط آزمون با بخش، فصل یا کتاب
     */
    public function quizable()
    {
        return $this->morphTo();
    }



    /**
     * سازنده آزمون
     */
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    /**
     * مدیر بررسی کننده
     */
    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



    /**
     * سوالات آزمون
     */
    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'quiz_question'
        )
        ->withPivot([

            'score',

            'sort_order',

        ])
        ->withTimestamps();
    }



    /**
     * شرکت‌های کاربران در آزمون
     */
    public function attempts()
    {
        return $this->hasMany(
            QuizAttempt::class
        );
    }

}
