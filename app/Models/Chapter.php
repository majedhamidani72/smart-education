<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'book_id',

        'title',

        'slug',

        'description',

        'thumbnail',

        'sort_order',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'is_active' => 'boolean',

        ];
    }



    // هر فصل متعلق به یک کتاب است
    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }



    // هر فصل چند بخش دارد
    public function sections()
    {
        return $this->hasMany(
            Section::class
        );
    }

}
