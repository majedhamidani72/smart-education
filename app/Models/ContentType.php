<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'title',

        // teaching
        // step_by_step
        // sample_questions

        'slug',

        'icon',

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

    public function contentItems(): HasMany
    {
        return $this->hasMany(
            ContentItem::class
        );
    }
}
