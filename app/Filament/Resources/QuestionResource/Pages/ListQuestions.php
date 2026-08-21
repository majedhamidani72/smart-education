<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // به‌جای فرم استاندارد «ایجاد» (که مسیر آموزشی را هر
            // بار از نو می‌خواست)، مستقیم به صفحه‌ی «افزودن سریع
            // سوال» می‌رود — که همان کار را بهتر انجام می‌دهد.
            Actions\Action::make('create')
                ->label('ایجاد سوال')
                ->icon('heroicon-o-plus')
                ->url(\App\Filament\Pages\AddQuestionsToBank::getUrl()),

        ];
    }

    /**
     * تب‌های وضعیت بالای لیست — دقیقاً مثل «محتوای آموزشی». عدد
     * هر تب از همان کوئری اصلی Resource (که برای معلم فقط
     * سوالات خودش را می‌شمارد، نه همه را) محاسبه می‌شود.
     */
    public function getTabs(): array
    {
        $baseQuery = fn() => QuestionResource::getEloquentQuery();

        return [

            'all' => Tab::make('همه')
                ->badge($baseQuery()->count()),

            'draft' => Tab::make('پیش نویس')
                ->badge($baseQuery()->where('status', 'draft')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft')),

            'pending' => Tab::make('در انتظار بررسی')
                ->badge($baseQuery()->where('status', 'pending')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),

            'approved' => Tab::make('تأیید شده')
                ->badge($baseQuery()->where('status', 'approved')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved')),

            'rejected' => Tab::make('رد شده')
                ->badge($baseQuery()->where('status', 'rejected')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected')),

        ];
    }
}
