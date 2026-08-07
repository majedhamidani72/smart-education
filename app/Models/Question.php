<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Question extends Model
{
    use HasFactory, SoftDeletes;



    // فیلدهای قابل ذخیره
    protected $fillable = [

        'created_by',
        // سازنده سوال (معلم یا ادمین)


        'reviewed_by',
        // ادمین بررسی کننده


        'question_text',
        // متن سوال


        'image_path',
        // تصویر سوال


        'explanation',
        // توضیح پاسخ صحیح


        'explanation_image_path',
        // تصویر توضیح پاسخ


        'difficulty',
        // سطح سختی سوال


        'default_score',
        // امتیاز پیش فرض سوال


        'status',
        // وضعیت سوال:
        // draft
        // pending
        // approved
        // rejected


        'rejection_reason',
        // دلیل رد سوال


        'is_active',
        // فعال یا غیرفعال بودن

    ];



    // تبدیل نوع داده‌ها
    protected function casts(): array
    {
        return [

            'default_score' => 'integer',

            'is_active' => 'boolean',

        ];
    }



    // =========================
    // Relationships
    // =========================


    // سازنده سوال
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    // ادمینی که سوال را بررسی کرده
    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



    // هر سوال می‌تواند در چند آزمون استفاده شود
    public function quizzes()
    {
        return $this->belongsToMany(
            Quiz::class,
            'quiz_question'
        )->withTimestamps();
    }



    // گزینه‌های این سوال
    public function options()
    {
        return $this->hasMany(
            QuestionOption::class
        );
    }
}
