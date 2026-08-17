<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'book_id',

        'title',

        'slug',

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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * کتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(
            Book::class
        );
    }

    /**
     * بخش‌های فصل
     */
    public function sections(): HasMany
    {
        return $this->hasMany(
            Section::class
        );
    }
}
