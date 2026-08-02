<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'chapter_id',

        'title',

        'slug',

        'description',

        'sort_order',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }



    // هر بخش متعلق به یک فصل است
    public function chapter()
    {
        return $this->belongsTo(
            Chapter::class
        );
    }



    // هر بخش چند محتوای آموزشی دارد
    public function contentItems()
    {
        return $this->hasMany(
            ContentItem::class
        );
    }

}
