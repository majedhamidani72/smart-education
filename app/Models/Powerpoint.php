<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Powerpoint extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('powerpoints');
    }

    protected $fillable = [
        'app_id', 'grade_id', 'book_id', 'chapter_id', 'title', 'slug',
        'description', 'file_path', 'preview_image', 'price', 'discount_price',
        'preview_pdf_path', 'slides_count', 'sample_slides_count', 'features',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'discount_price' => 'integer',
            'slides_count' => 'integer',
            'sample_slides_count' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function app() { return $this->belongsTo(App::class); }
    public function grade() { return $this->belongsTo(Grade::class); }
    public function book() { return $this->belongsTo(Book::class); }
    public function chapter() { return $this->belongsTo(Chapter::class); }

    public function finalPrice(): int
    {
        return $this->discount_price !== null && $this->discount_price < $this->price
            ? $this->discount_price : $this->price;
    }
}
