<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * آخرین خریدهای موفق — «چه کسی چه چیزی خریده».
 * --------------------------------------------------------------------
 * فقط‌خواندنی؛ هیچ عملیاتی روی رکوردها انجام نمی‌شود (نه ویرایش،
 * نه حذف) — صرفاً یک نمای گزارشی سریع، جدا از خودِ Resource خرید.
 */
class RecentPurchasesWidget extends BaseWidget
{
    protected static ?string $heading = 'آخرین خریدهای موفق';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['SuperAdmin', 'Admin']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Purchase::query()
                    ->where('status', 'paid')
                    ->with(['user', 'items.plan.planable'])
                    ->latest('paid_at')
            )
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('دانش‌آموز')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('user.mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items')
                    ->label('خریداری‌شده')
                    ->getStateUsing(function (Purchase $record) {

                        return $record->items
                            ->map(fn($item) => $item->plan?->title
                                ?? $item->plan?->planable?->title
                                ?? '—')
                            ->join('، ');
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('payable_amount')
                    ->label('مبلغ')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->formatStateUsing(fn($state) => \App\Support\JalaliDate::format($state))
                    ->sortable(),

            ]);
    }
}
