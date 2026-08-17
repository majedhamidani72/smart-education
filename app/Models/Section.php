<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'chapter_id',

        'title',

        'slug',

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
     * فصل
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(
            Chapter::class
        );
    }

    /**
     * محتواهای آموزشی
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(
            ContentItem::class
        );
    }
}
