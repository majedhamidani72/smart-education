<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepByStepPage extends Model
{
    use HasFactory;

    protected $fillable = [

        'step_by_step_id',

        'title',

        'page_number',

        'image',

        'sort_order',

        'is_free',

    ];

    protected function casts(): array
    {
        return [

            'is_free' => 'boolean',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function stepByStep(): BelongsTo
    {
        return $this->belongsTo(
            StepByStep::class,
            'step_by_step_id'
        );
    }
}
