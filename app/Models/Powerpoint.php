<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Powerpoint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'app_id', 'grade_id', 'book_id', 'chapter_id', 'title', 'slug',
        'description', 'file_path', 'preview_image', 'price', 'discount_price',
        'slides_count', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['price' => 'integer', 'discount_price' => 'integer', 'slides_count' => 'integer', 'is_active' => 'boolean'];
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
