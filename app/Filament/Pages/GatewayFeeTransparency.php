<?php

namespace App\Filament\Pages;

use App\Services\SettingService;
use Filament\Pages\Page;

/**
 * شفافیت کارمزد درگاه‌های پرداخت
 * --------------------------------------------------------------------
 * یک صفحه‌ی صرفاً نمایشی (بدون فرم/جدول قابل‌ویرایش) که وضعیت
 * فعلیِ واقعیِ تنظیمات کارمزد هر درگاه و درصد پیش‌فرض سهم معلم را
 * مستقیم از تنظیمات سیستم می‌خواند و نشان می‌دهد — تا معلم، ادمین
 * و سوپرادمین همیشه دقیقاً بدانند «همین الان» چطور محاسبه می‌شود.
 * چون مستقیم از تنظیمات واقعی خوانده می‌شود، هیچ‌وقت نیاز به
 * به‌روزرسانی دستی این صفحه نیست — با تغییر تنظیمات، خودش هم
 * به‌روز می‌شود.
 */
class GatewayFeeTransparency extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationGroup = 'مدیریت مالی';

    protected static ?string $navigationLabel = 'شفافیت کارمزد درگاه‌ها';

    protected static ?string $title = 'شفافیت کارمزد درگاه‌های پرداخت';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.gateway-fee-transparency';

    /**
     * برخلاف بقیه‌ی صفحات مالی، این صفحه عمداً برای معلم هم باز
     * است — چون هدفش دقیقاً شفافیت برای خودِ معلم‌هاست.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            && (
                $user->hasRole('SuperAdmin')
                || $user->hasRole('Admin')
                || $user->hasRole('Teacher')
            );
    }

    public function getFeeData(): array
    {
        $settings = app(SettingService::class);

        return [

            'zibal' => [
                'percentage' => $settings->getValue('gateway_fee_zibal_percentage', '1'),
                'min' => (int) $settings->getValue('gateway_fee_zibal_min', '2000'),
                'max' => (int) $settings->getValue('gateway_fee_zibal_max', '20000'),
                'vat' => $settings->getValue('gateway_fee_zibal_vat_percentage', '10'),
            ],

            'bazaar' => [
                'percentage' => $settings->getValue('gateway_fee_bazaar', '15'),
            ],

            'myket' => [
                'percentage' => $settings->getValue('gateway_fee_myket', '15'),
            ],

            'teacher_default_percentage' => $settings->defaultTeacherCommissionPercentage(),

        ];
    }
}
