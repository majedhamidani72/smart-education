<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherAgreement extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'teacher_id',

        'agreement_type',

        'agreement_version',

        'accepted_at',

        'ip_address',

        'user_agent',

    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'accepted_at' => 'datetime',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    /**
     * معلم یا ادمینی که قوانین را پذیرفته است.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(

            User::class,

            'teacher_id'

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Query Helpers
    |--------------------------------------------------------------------------
    */


    /**
     * آخرین نسخه پذیرفته شده قوانین
     */
    public static function latestAgreement(
        int $teacherId,
        string $type
    ): ?self {

        return static::query()

            ->where(
                'teacher_id',
                $teacherId
            )

            ->where(
                'agreement_type',
                $type
            )

            ->latest(
                'accepted_at'
            )

            ->first();

    }


    /**
     * بررسی پذیرش نسخه فعلی قوانین
     */
    public static function hasAccepted(
        int $teacherId,
        string $type,
        string $version
    ): bool {

        return static::query()

            ->where(
                'teacher_id',
                $teacherId
            )

            ->where(
                'agreement_type',
                $type
            )

            ->where(
                'agreement_version',
                $version
            )

            ->exists();

    }


    /**
     * ثبت پذیرش قوانین
     */
    public static function accept(
        int $teacherId,
        string $type,
        string $version,
        ?string $ip,
        ?string $userAgent
    ): self {

        return static::create([

            'teacher_id' => $teacherId,

            'agreement_type' => $type,

            'agreement_version' => $version,

            'accepted_at' => now(),

            'ip_address' => $ip,

            'user_agent' => $userAgent,

        ]);

    }


    /**
     * حذف پذیرش نسخه‌های قبلی
     */
    public static function removeOldVersions(
        int $teacherId,
        string $type,
        string $currentVersion
    ): void {

        static::query()

            ->where(
                'teacher_id',
                $teacherId
            )

            ->where(
                'agreement_type',
                $type
            )

            ->where(
                'agreement_version',
                '!=',
                $currentVersion
            )

            ->delete();

    }
}
