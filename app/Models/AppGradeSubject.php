<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppGradeSubject extends Model
{
    use HasFactory;

    protected $table = 'app_grade_subjects';

    protected $fillable = [

        'app_id',

        'grade_id',

        'subject_id',

        'is_active',

        'sort_order',

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

    public function app(): BelongsTo
    {
        return $this->belongsTo(
            App::class
        );
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(
            Grade::class
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(
            Subject::class
        );
    }

    public function books(): HasMany
    {
        return $this->hasMany(
            Book::class
        );
    }
}
