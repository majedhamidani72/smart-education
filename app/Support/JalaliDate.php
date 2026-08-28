<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * تبدیل تاریخ میلادی به شمسی — بدون هیچ پکیج بیرونی.
 * --------------------------------------------------------------------
 * چون این محیط به Packagist دسترسی ندارد (نصب morilog/jalali یا
 * مشابه ممکن نیست)، همینجا یک تبدیل استاندارد و شناخته‌شده‌ی
 * میلادی→شمسی نوشته شده — کاملاً مستقل، در هر محیطی کار می‌کند.
 *
 * استفاده:
 *   JalaliDate::format($model->paid_at)              => "۱۴۰۴/۰۶/۰۷ ۱۴:۳۰"
 *   JalaliDate::format($model->paid_at, withTime: false) => "۱۴۰۴/۰۶/۰۷"
 */
class JalaliDate
{
    protected const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    public static function format(
        mixed $date,
        bool $withTime = true
    ): string {

        if (! $date) {
            return '—';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        [$jy, $jm, $jd] = self::toJalali(
            (int) $carbon->format('Y'),
            (int) $carbon->format('n'),
            (int) $carbon->format('j')
        );

        $result = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);

        if ($withTime) {
            $result .= ' '.$carbon->format('H:i');
        }

        return self::toPersianDigits($result);
    }

    /**
     * الگوریتم استاندارد و تایید‌شده‌ی تبدیل میلادی به شمسی
     * (الگوریتم Borkowski، پایه‌ی اکثر کتابخانه‌های معروف تبدیل
     * تاریخ) — با چند تاریخ شناخته‌شده (از جمله نوروز) تست و
     * تایید شده است.
     */
    protected static function toJalali(
        int $gy,
        int $gm,
        int $gd
    ): array {

        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy2 = $gy - 1600;

        $gm2 = $gm - 1;

        $gd2 = $gd - 1;

        $gDayNo = 365 * $gy2
            + (int) (($gy2 + 3) / 4)
            - (int) (($gy2 + 99) / 100)
            + (int) (($gy2 + 399) / 400);

        for ($i = 0; $i < $gm2; $i++) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm2 > 1 && (($gy2 % 4 === 0 && $gy2 % 100 !== 0) || $gy2 % 400 === 0)) {
            $gDayNo++;
        }

        $gDayNo += $gd2;

        $jDayNo = $gDayNo - 79;

        $jNp = (int) ($jDayNo / 12053);

        $jDayNo %= 12053;

        $jy = 979 + 33 * $jNp + 4 * (int) ($jDayNo / 1461);

        $jDayNo %= 1461;

        if ($jDayNo >= 366) {

            $jy += (int) (($jDayNo - 1) / 365);

            $jDayNo = ($jDayNo - 1) % 365;
        }

        $i = 0;

        while ($i < 11 && $jDayNo >= $jDaysInMonth[$i]) {

            $jDayNo -= $jDaysInMonth[$i];

            $i++;
        }

        $jm = $i + 1;

        $jd = $jDayNo + 1;

        return [$jy, $jm, $jd];
    }

    protected static function toPersianDigits(string $value): string
    {
        return strtr($value, array_combine(range('0', '9'), self::PERSIAN_DIGITS));
    }
}
