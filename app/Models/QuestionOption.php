<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;



    protected $fillable = [

        'question_id',
        // سوال مربوطه


        'option_text',
        // متن گزینه


        'image_path',
        // تصویر گزینه


        'is_correct',
        // گزینه صحیح


        'recommended_content_item_id',
        // پیشنهاد مطالعه اگر دانش‌آموز این گزینه (اشتباه) را زد


        'sort_order',
        // ترتیب نمایش

    ];



    protected function casts(): array
    {
        return [

            'is_correct' => 'boolean',

        ];
    }



    // =========================
    // Relationships
    // =========================


    // هر گزینه متعلق به یک سوال است
    public function question()
    {
        return $this->belongsTo(
            Question::class
        );
    }

    /**
     * محتوایی که اگر دانش‌آموز این گزینه‌ی غلط را انتخاب کند،
     * برای مطالعه به او پیشنهاد می‌شود.
     */
    public function recommendedContentItem()
    {
        return $this->belongsTo(
            ContentItem::class,
            'recommended_content_item_id'
        );
    }

}
