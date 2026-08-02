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
        // ارتباط با بخش، فصل یا کتاب


        'created_by',
        // سازنده آزمون (معلم یا ادمین)


        'reviewed_by',
        // ادمینی که بررسی کرده


        'title',
        // عنوان آزمون


        'description',
        // توضیحات


        'questions_count',
        // تعداد سوالات نمایشی


        'time_limit',
        // زمان آزمون به دقیقه


        'passing_percentage',
        // درصد قبولی


        'max_attempts',
        // تعداد دفعات شرکت


        'randomize_questions',
        // تصادفی بودن سوالات


        'randomize_options',
        // تصادفی بودن گزینه‌ها


        'show_result',
        // نمایش نتیجه


        'show_correct_answers',
        // نمایش پاسخ صحیح


        'is_free',
        // رایگان یا پولی


        'status',
        // draft
        // pending
        // active
        // inactive


        'rejection_reason',
        // دلیل رد


        'published_at',
        // زمان انتشار

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



    // =========================
    // Relationships
    // =========================


    // ارتباط با بخش، فصل یا کتاب
    public function quizable()
    {
        return $this->morphTo();
    }



    // سازنده آزمون
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    // ادمین بررسی کننده
    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



    // سوالات آزمون
    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'quiz_question'
        );
    }



    // شرکت‌های کاربران در آزمون
    public function attempts()
    {
        return $this->hasMany(
            QuizAttempt::class
        );
    }

}
