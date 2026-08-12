<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [

        'content_item_id',

        'question_topic_id',

        'created_by',

        'reviewed_by',

        'question_text',

        'image_path',

        'explanation',

        'explanation_image_path',

        'difficulty',

        'default_score',

        'status',

        'rejection_reason',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'default_score' => 'integer',

            'is_active' => 'boolean',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    /**
     * محتوای آموزشی مربوط به سوال
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(
            ContentItem::class,
            'content_item_id'
        );
    }



    /**
     * موضوع سوال
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(
            QuestionTopic::class,
            'question_topic_id'
        );
    }



    /**
     * سازنده سوال
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    /**
     * مدیر بررسی کننده
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



    /**
     * آزمون‌هایی که این سوال داخل آن‌ها استفاده شده
     */
    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(
            Quiz::class,
            'quiz_question'
        )
        ->withPivot([

            'score',

            'sort_order',

        ])
        ->withTimestamps();
    }



    /**
     * گزینه‌های سوال
     */
    public function options(): HasMany
    {
        return $this->hasMany(
            QuestionOption::class
        );
    }



    /**
     * پاسخ‌های ثبت شده دانش‌آموزان
     */
    public function questionAttempts(): HasMany
    {
        return $this->hasMany(
            QuestionAttempt::class
        );
    }

}
