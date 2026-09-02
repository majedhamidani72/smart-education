<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StepByStep extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'content_item_id',

        'uploaded_by',

        'directory',

        'filename',

        'original_name',

        'extension',

        'mime_type',

        'file_size',

        'download_allowed',

        'processing_status',

        'approved_by',

        'approved_at',

        'rejected_reason',

    ];

    protected function casts(): array
    {
        return [

            'file_size' => 'integer',

            'download_allowed' => 'boolean',

            'approved_at' => 'datetime',

        ];
    }

    /*
|--------------------------------------------------------------------------
| Relationships
|--------------------------------------------------------------------------
*/

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function pages(): HasMany
    {
        return $this->hasMany(
            StepByStepPage::class,
            'step_by_step_id'
        );
    }

    /*
|--------------------------------------------------------------------------
| Accessors
|--------------------------------------------------------------------------
*/

    public function imageUrl(): Attribute
    {
        return Attribute::make(

            get: fn() =>

            $this->directory && $this->filename

                ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                    $this->directory . '/' . $this->filename
                )

                : null

        );
    }



    public function fileSizeReadable(): Attribute
    {
        return Attribute::make(

            get: function () {

                if (! $this->file_size) {

                    return null;
                }

                return round(

                    $this->file_size / 1024 / 1024,

                    2

                ) . ' MB';
            }

        );
    }

    /*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

    public function fullPath(): string
    {
        return storage_path(

            'app/public/' . $this->directory . '/' . $this->filename

        );
    }

    public function isApproved(): bool
    {
        return $this->processing_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->processing_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->processing_status === 'rejected';
    }
}
