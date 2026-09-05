<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Video extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('videos');
    }

    protected $fillable = [

        'content_item_id',

        'uploaded_by',

        'directory',

        'filename',

        'original_name',

        'extension',

        'mime_type',

        'file_size',

        'duration',

        'quality',

        'thumbnail_path',

        'views_count',

        'download_allowed',

    ];

    protected function casts(): array
    {
        return [

            'file_size' => 'integer',

            'duration' => 'integer',

            'views_count' => 'integer',

            'download_allowed' => 'boolean',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * محتوای آموزشی
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

    /**
     * آپلود کننده
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * آدرس فایل ویدئو
     */
    public function videoUrl(): Attribute
    {
        return Attribute::make(

            get: fn () =>

                $this->directory && $this->filename

                    // فایل‌ها روی دیسک "public" (storage/app/public)
                    // ذخیره می‌شوند، نه مستقیم داخل پوشه‌ی public/.
                    ? Storage::disk('public')->url(
                        $this->directory . '/' . $this->filename
                    )

                    : null

        );
    }

    /**
     * آدرس تصویر بندانگشتی
     */
    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(

            get: fn () =>

                $this->thumbnail_path

                    ? Storage::disk('public')->url(
                        $this->thumbnail_path
                    )

                    : null

        );
    }

    /**
     * حجم فایل به صورت خوانا
     */
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

    /**
     * مسیر کامل فایل
     */
    public function fullPath(): string
    {
        return Storage::disk('public')->path(

            $this->directory . '/' . $this->filename

        );
    }
}
