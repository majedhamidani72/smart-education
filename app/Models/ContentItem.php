<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'section_id',

        'content_type_id',

        'created_by',

        'reviewed_by',

        'title',

        'slug',

        'description',

        'page_number',

        'thumbnail',

        'is_free',

        'status',

        'rejection_reason',

        'sort_order',

        'published_at',

        'reviewed_at',

    ];

    protected function casts(): array
    {
        return [

            'is_free' => 'boolean',

            'published_at' => 'datetime',

            'reviewed_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * بخش آموزشی
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(
            Section::class
        );
    }

    /**
     * نوع محتوا
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(
            ContentType::class
        );
    }

    /**
     * ایجاد کننده محتوا
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * بررسی کننده محتوا
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    /**
     * ویدئو
     */
    public function video(): HasOne
    {
        return $this->hasOne(
            Video::class
        );
    }

    /**
     * گام به گام
     */
    public function stepByStep(): HasOne
    {
        return $this->hasOne(
            StepByStep::class
        );
    }

    /**
     * فایل PDF
     */
    public function pdfFile(): HasOne
    {
        return $this->hasOne(
            PdfFile::class
        );
    }

    /**
     * صفحات گام به گام
     */
    

    /**
     * نمونه سوالات
     */
    public function sampleQuestions(): HasMany
    {
        return $this->hasMany(
            SampleQuestion::class
        );
    }

    /**
     * تایید محتوا
     */
    public function approval(): HasOne
    {
        return $this->hasOne(
            ContentApproval::class
        );
    }
}
