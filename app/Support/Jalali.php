<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/*
|--------------------------------------------------------------------------
| تبدیل تاریخ میلادی به شمسی
|--------------------------------------------------------------------------
| این کلاس بدون وابستگی به هیچ پکیج بیرونی (مثل morilog/jalali)
| کار می‌کند، چون Packagist در محیط توسعه‌ی فعلی در دسترس نبود.
| الگوریتم استاندارد تبدیل میلادی به جلالی (Borkowski) است که
| سال‌هاست در پروژه‌های فارسی استفاده می‌شود.
|
| استفاده در Filament:
|   ->formatStateUsing(fn ($state) => Jalali::format($state))
*/
class Jalali
{
    /**
     * یک تاریخ (Carbon یا null) را به رشته‌ی شمسی تبدیل می‌کند.
     *
     * @param  CarbonInterface|string|null  $date
     * @param  string  $format  فقط از Y (سال)، m (ماه ۲رقمی)،
     *                          d (روز ۲رقمی)، H:i (ساعت:دقیقه)
     *                          پشتیبانی می‌شود.
     */
    public static function format(
        CarbonInterface|string|null $date,
        string $format = 'Y/m/d H:i'
    ): ?string {

        if (! $date) {
            return null;
        }

        $carbon = $date instanceof CarbonInterface
            ? $date
            : Carbon::parse($date);

        [$jy, $jm, $jd] = self::gregorianToJalali(
            (int) $carbon->format('Y'),
            (int) $carbon->format('n'),
            (int) $carbon->format('j')
        );

        $replacements = [
            'Y' => (string) $jy,
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'H' => $carbon->format('H'),
            'i' => $carbon->format('i'),
        ];

        return strtr($format, $replacements);
    }

    /**
     * الگوریتم تبدیل سال/ماه/روز میلادی به جلالی.
     *
     * @return array{0: int, 1: int, 2: int} [سال, ماه, روز]
     */
    protected static function gregorianToJalali(
        int $gy,
        int $gm,
        int $gd
    ): array {

        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $jy = ($gy <= 1600) ? 0 : 979;

        $gy -= ($gy <= 1600) ? 621 : 1600;

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

        $days = (365 * $gy)
            + ((int) (($gy2 + 3) / 4))
            - ((int) (($gy2 + 99) / 100))
            + ((int) (($gy2 + 399) / 400))
            - 80
            + $gd
            + $g_d_m[$gm - 1];

        $jy += 33 * ((int) ($days / 12053));

        $days %= 12053;

        $jy += 4 * ((int) ($days / 1461));

        $days %= 1461;

        if ($days > 365) {

            $jy += (int) (($days - 1) / 365);

            $days = ($days - 1) % 365;
        }

        if ($days < 186) {

            $jm = 1 + (int) ($days / 31);

            $jd = 1 + ($days % 31);

        } else {

            $jm = 7 + (int) (($days - 186) / 30);

            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }
}
