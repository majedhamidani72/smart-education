<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
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

        'duration',

        'quality',

        'thumbnail_path',

        'views_count',

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

            'duration' => 'integer',

            'views_count' => 'integer',

            'download_allowed' => 'boolean',

            'approved_at' => 'datetime',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    /**
     * اطلاعات آموزشی ویدئو
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }



    /**
     * معلم یا کاربر آپلود کننده
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }



    /**
     * تایید کننده محتوا
     */
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


    public function videoUrl(): Attribute
    {
        return Attribute::make(

            get: fn () =>
                $this->directory && $this->filename

                    ? asset(
                        $this->directory.'/'.$this->filename
                    )

                    : null

        );
    }



    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(

            get: fn () =>
                $this->thumbnail_path

                    ? asset(
                        $this->thumbnail_path
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
        return public_path(
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
