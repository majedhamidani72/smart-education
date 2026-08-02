<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentItem extends Model
{
    use HasFactory, SoftDeletes;



    // فیلدهایی که اجازه ذخیره دارند
    protected $fillable = [

        'section_id',
        // بخش آموزشی


        'content_type_id',
        // نوع محتوا


        'created_by',
        // سازنده محتوا (ادمین یا معلم)


        'reviewed_by',
        // مدیر بررسی کننده


        'title',
        // عنوان محتوا


        'slug',
        // نام یکتا


        'description',
        // توضیحات


        'page_number',
        // شماره صفحه کتاب


        'thumbnail',
        // تصویر شاخص


        'is_free',
        // رایگان یا پولی


        'status',
        // draft
        // pending
        // approved
        // rejected
        // published


        'rejection_reason',
        // دلیل رد شدن


        'sort_order',
        // ترتیب نمایش


        'published_at',
        // زمان انتشار

    ];



    protected function casts(): array
    {
        return [

            'is_free' => 'boolean',

            'published_at' => 'datetime',

        ];
    }



    // =========================
    // Relationships
    // =========================


    // محتوا متعلق به یک بخش است
    public function section()
    {
        return $this->belongsTo(
            Section::class
        );
    }



    // نوع محتوا
    public function contentType()
    {
        return $this->belongsTo(
            ContentType::class
        );
    }



    // کسی که محتوا را ساخته
    // (معلم یا ادمین)
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }



    // مدیری که محتوا را بررسی کرده
    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }



    // ویدئو
    public function video()
    {
        return $this->hasOne(
            Video::class
        );
    }



    // فایل PDF
    public function pdfFile()
    {
        return $this->hasOne(
            PdfFile::class
        );
    }



    // صفحات گام به گام
    public function stepByStepPages()
    {
        return $this->hasMany(
            StepByStepPage::class
        );
    }



    // نمونه سوال
    public function sampleQuestions()
    {
        return $this->hasMany(
            SampleQuestion::class
        );
    }



    // تایید محتوا
    public function approval()
    {
        return $this->hasOne(
            ContentApproval::class
        );
    }

}
