<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Quiz $quiz) {
            if ($quiz->is_template) {
                $quiz->generatedQuizzes()->get()->each->delete();
            }
        });

        static::restored(function (Quiz $quiz) {
            if ($quiz->is_template) {
                app(\App\Services\QuizTemplateService::class)->sync($quiz);
            }
        });
    }


    protected $fillable = [

        'quizable_type',

        'quizable_id',

        'term_scope',

        'is_template',

        'template_id',

        'template_book_id',

        'template_scope',

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

            'is_template' => 'boolean',

            'published_at' => 'datetime',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * ارتباط چندریختی آزمون
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
     * بررسی کننده
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
     * تلاش‌های کاربران
     */
    public function attempts()
    {
        return $this->hasMany(
            QuizAttempt::class
        );
    }

    public function template()
    {
        return $this->belongsTo(self::class, 'template_id');
    }

    public function generatedQuizzes()
    {
        return $this->hasMany(self::class, 'template_id');
    }

    public function templateBook()
    {
        return $this->belongsTo(Book::class, 'template_book_id');
    }



    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */



    /**
     * دریافت کتاب مربوط به آزمون
     *
     * آزمون می‌تواند:
     *
     * Book
     * Chapter
     * Section
     *
     * باشد.
     */
    public function getBookAttribute(): ?Book
    {

        if ($this->quizable instanceof Book) {

            return $this->quizable;

        }



        if ($this->quizable instanceof Chapter) {

            return $this->quizable->book;

        }



        if ($this->quizable instanceof Section) {

            return $this->quizable
                ->chapter
                ->book;

        }



        return null;
    }



    /**
     * بررسی نوع آزمون
     */
    public function isBookQuiz(): bool
    {
        return $this->quizable_type === Book::class;
    }



    public function isChapterQuiz(): bool
    {
        return $this->quizable_type === Chapter::class;
    }



    public function isSectionQuiz(): bool
    {
        return $this->quizable_type === Section::class;
    }

}
