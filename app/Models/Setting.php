<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'key',

        'value',

        'group',

        'type',

        'description',

        'is_public',

    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'is_public' => 'boolean',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    /**
     * دریافت مقدار یک تنظیم
     */
    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {

        return static::query()
            ->where('key', $key)
            ->value('value')
            ?? $default;

    }


    /**
     * ایجاد یا بروزرسانی تنظیم
     */
    public static function setValue(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'text',
        ?string $description = null,
        bool $isPublic = false
    ): self {

        return static::updateOrCreate(

            [

                'key' => $key,

            ],

            [

                'value' => $value,

                'group' => $group,

                'type' => $type,

                'description' => $description,

                'is_public' => $isPublic,

            ]

        );

    }


    /**
     * بررسی وجود تنظیم
     */
    public static function has(
        string $key
    ): bool {

        return static::query()
            ->where('key', $key)
            ->exists();

    }


    /**
     * حذف تنظیم
     */
    public static function remove(
        string $key
    ): bool {

        return (bool) static::query()
            ->where('key', $key)
            ->delete();

    }


    /**
     * دریافت تمام تنظیمات یک گروه
     */
    public static function byGroup(
        string $group
    )
    {
        return static::query()
            ->where('group', $group)
            ->orderBy('key')
            ->get();
    }
}
