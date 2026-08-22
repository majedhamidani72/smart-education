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

        'book_id',

        'chapter_id',

        'section_id',

        'question_topic_id',

        'created_by',

        'reviewed_by',

        'question_text',

        'image_path',

        'explanation',

        'explanation_image_path',

        'recommendation_text',

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
    | Relations
    |--------------------------------------------------------------------------
    */


    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(
            ContentItem::class,
            'content_item_id'
        );
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }



    public function topic(): BelongsTo
    {
        return $this->belongsTo(
            QuestionTopic::class,
            'question_topic_id'
        );
    }



    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



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



    public function options(): HasMany
    {
        return $this->hasMany(
            QuestionOption::class
        );
    }



    public function questionAttempts(): HasMany
    {
        return $this->hasMany(
            QuestionAttempt::class
        );
    }



    // نکته: قبلاً اینجا یک متد getBookAttribute() بود که کتاب را
    // از مسیر content_item→section→chapter→book محاسبه می‌کرد —
    // این متد، رابطه‌ی واقعی book() (که مستقیم روی ستون book_id
    // کار می‌کند) را بی‌صدا override می‌کرد و همیشه null برمی‌
    // گرداند برای سوالاتی که content_item نداشتند، حتی وقتی
    // book_id واقعی‌شان پر بود. حذف شد چون دیگر لازم نیست — رابطه‌ی
    // book() در بالای همین فایل مستقیم کار می‌کند.


}
