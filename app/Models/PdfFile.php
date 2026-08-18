<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PdfFile extends Model
{
    use HasFactory, SoftDeletes;

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

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function fileUrl(): Attribute
    {
        return Attribute::make(

            get: fn() =>

                $this->directory && $this->filename

                    // فایل‌ها توسط Filament روی دیسک "public"
                    // (یعنی storage/app/public، نه پوشه‌ی public/
                    // پروژه به‌طور مستقیم) ذخیره می‌شوند؛ برای همین
                    // باید از Storage::disk('public')->url()
                    // استفاده کرد، نه asset() خام.
                    ? Storage::disk('public')->url(
                        $this->directory.'/'.$this->filename
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

                ).' MB';

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
        // مسیر واقعی فایل روی دیسک "public"
        // (storage/app/public/...)
        return Storage::disk('public')->path(

            $this->directory.'/'.$this->filename

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
