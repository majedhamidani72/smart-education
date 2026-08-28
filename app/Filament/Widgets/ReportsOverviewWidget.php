<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Models\Subscription;
use App\Models\TeacherEarning;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * آمار کلی داشبورد — اولین چیزی که ادمین با ورود می‌بیند.
 * --------------------------------------------------------------------
 * فقط‌خواندنی و کاملاً مستقل از بقیه‌ی پنل است — هیچ رکورد
 * دیگری را تغییر نمی‌دهد، فقط از جدول‌های موجود می‌خواند.
 */
class ReportsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    /**
     * فقط ادمین/سوپرادمین این آمار کلی (درآمد کل، همه‌ی
     * دانش‌آموزان) را می‌بینند — نه معلم، چون شامل اطلاعات مالی
     * فراتر از درآمد خودِ اوست.
     */
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['SuperAdmin', 'Admin']) ?? false;
    }

    protected function getStats(): array
    {
        $activeStudents = Subscription::query()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->distinct('user_id')
            ->count('user_id');

        $totalRevenue = Purchase::query()
            ->where('status', 'paid')
            ->sum('payable_amount');

        $thisMonthRevenue = Purchase::query()
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('payable_amount');

        $pendingSettlement = TeacherEarning::query()
            ->where('status', 'pending')
            ->sum('amount');

        return [

            Stat::make('دانش‌آموزان با دسترسی فعال', number_format($activeStudents))
                ->description('کسانی که الان اشتراک فعال دارند')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('درآمد کل', number_format($totalRevenue).' تومان')
                ->description('مجموع تمام خریدهای موفق')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('درآمد این ماه', number_format($thisMonthRevenue).' تومان')
                ->description('از اول ماه میلادی جاری تا الان')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('در انتظار تسویه با معلمان', number_format($pendingSettlement).' تومان')
                ->description('سهم معلمانی که هنوز تسویه نشده')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

        ];
    }
}
