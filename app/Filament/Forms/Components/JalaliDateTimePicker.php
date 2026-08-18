<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

/*
|--------------------------------------------------------------------------
| فیلد سفارشی: انتخاب‌گر تاریخ و ساعت شمسی
|--------------------------------------------------------------------------
| چون نصب پکیج‌های PHP جدید (مثل morilog/jalali) در این محیط
| توسعه ممکن نبود (Packagist در دسترس نیست)، این فیلد کاملاً با
| جاوااسکریپت خالص (Alpine.js، که همراه خودِ Filament می‌آید) کار
| می‌کند — تبدیل شمسی↔میلادی هم سمت مرورگر انجام می‌شود، نه سمت
| سرور. مقداری که در پایگاه‌داده ذخیره می‌شود همچنان میلادی و
| استاندارد است (Y-m-d H:i:s)؛ فقط نمایش و انتخاب برای کاربر
| شمسی است.
|
| استفاده:
|   JalaliDateTimePicker::make('published_at')
|       ->label('زمان انتشار')
|       ->default(now())
*/
class JalaliDateTimePicker extends Field
{
    protected string $view = 'filament.forms.components.jalali-date-time-picker';
}
