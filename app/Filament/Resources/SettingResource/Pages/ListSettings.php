<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\ListRecords;

/**
 * لیست تنظیمات سیستم
 * --------------------------------------------------------------------
 * به‌جای جدول استاندارد Filament (که عنوان دسته را به‌صورت یک ردیف
 * افقی و قابل‌جمع‌شدن نشان می‌دهد)، اینجا یک جدول کاملاً سفارشی
 * ساخته شده: عنوان هر دسته به‌صورت عمودی، سمت راست، و کشیده روی
 * تمام ردیف‌های همان دسته نمایش داده می‌شود؛ و همه‌ی ردیف‌های یک
 * دسته یک رنگ پس‌زمینه‌ی مشترک دارند — تا بدون نیاز به خواندن
 * عنوان، فقط از روی رنگ هم بشود فهمید کدام تنظیمات به‌هم مربوطند.
 */
class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.setting-resource.pages.list-settings';

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * تنظیمات را دسته‌بندی‌شده (بر اساس ستون group) برمی‌گرداند —
     * هر دسته با رنگ ثابت و همیشه‌یکسان خودش (بر اساس نام دسته،
     * نه ترتیب تصادفی).
     */
    public function getGroupedSettings(): array
    {
        $palette = [
            ['bg' => 'rgba(99,102,241,0.08)', 'accent' => 'rgb(99,102,241)'],
            ['bg' => 'rgba(20,184,166,0.08)', 'accent' => 'rgb(20,184,166)'],
            ['bg' => 'rgba(234,179,8,0.08)', 'accent' => 'rgb(202,138,4)'],
            ['bg' => 'rgba(217,70,239,0.08)', 'accent' => 'rgb(217,70,239)'],
            ['bg' => 'rgba(239,68,68,0.08)', 'accent' => 'rgb(220,38,38)'],
            ['bg' => 'rgba(100,116,139,0.08)', 'accent' => 'rgb(71,85,105)'],
        ];

        $settings = Setting::query()
            ->orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        $groups = [];

        $i = 0;

        foreach ($settings as $groupName => $items) {

            $colors = $palette[$i % count($palette)];

            $groups[] = [
                'name' => $groupName,
                'items' => $items,
                'bg' => $colors['bg'],
                'accent' => $colors['accent'],
            ];

            $i++;
        }

        return $groups;
    }
}
