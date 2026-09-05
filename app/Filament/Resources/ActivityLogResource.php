<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

/**
 * گزارش فعالیت‌ها (لاگ حسابرسی)
 * --------------------------------------------------------------------
 * ثبت دائمی و قابل‌جستجوی «چه کسی، چه زمانی، چه کاری روی کدام رکورد
 * انجام داد» — مستقل از فایل‌های لاگ متنی (که ممکن است پاک یا
 * Rotate شوند). برای پاسخ‌گویی به شکایت‌ها یا اختلافات کاربران در
 * آینده استفاده می‌شود. کاملاً فقط-خواندنی است؛ فقط سوپرادمین دسترسی
 * دارد چون شامل IP و اطلاعات دیگر کاربران است.
 */
class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'مدیریت سیستم';

    protected static ?string $navigationLabel = 'گزارش فعالیت‌ها';

    protected static ?string $modelLabel = 'فعالیت';

    protected static ?string $pluralModelLabel = 'گزارش فعالیت‌ها';

    protected static ?int $navigationSort = 90;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    protected static function logNameLabel(?string $logName): string
    {
        return match ($logName) {
            'auth' => 'ورود / احراز هویت',
            'users' => 'کاربران',
            'purchases' => 'خریدها',
            'payment_transactions' => 'تراکنش‌های مالی',
            'content_items' => 'محتوای آموزشی',
            'videos' => 'ویدیوها',
            'powerpoints' => 'پاورپوینت‌ها',
            default => $logName ?: '—',
        };
    }

    protected static function eventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'ایجاد',
            'updated' => 'ویرایش',
            'deleted' => 'حذف',
            default => $event ?: '—',
        };
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('جزئیات فعالیت')

                ->columns(2)

                ->schema([

                    TextEntry::make('created_at')
                        ->label('زمان')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('log_name')
                        ->label('دسته')
                        ->badge()
                        ->formatStateUsing(fn($state) => self::logNameLabel($state)),

                    TextEntry::make('causer.name')
                        ->label('انجام‌دهنده')
                        ->placeholder('سیستم (بدون کاربر مشخص)'),

                    TextEntry::make('causer.mobile')
                        ->label('موبایل انجام‌دهنده')
                        ->placeholder('—'),

                    TextEntry::make('event')
                        ->label('نوع عملیات')
                        ->badge()
                        ->formatStateUsing(fn($state) => self::eventLabel($state)),

                    TextEntry::make('subject_type')
                        ->label('نوع رکورد')
                        ->formatStateUsing(fn($state) => $state ? class_basename($state) : '—'),

                    TextEntry::make('description')
                        ->label('توضیح')
                        ->columnSpanFull(),

                    KeyValueEntry::make('properties.attributes')
                        ->label('مقادیر جدید')
                        ->visible(fn($record) => filled($record->getExtraProperty('attributes')))
                        ->columnSpanFull(),

                    KeyValueEntry::make('properties.old')
                        ->label('مقادیر قبلی')
                        ->visible(fn($record) => filled($record->getExtraProperty('old')))
                        ->columnSpanFull(),

                    KeyValueEntry::make('properties.ip')
                        ->label('آدرس IP')
                        ->visible(fn($record) => filled($record->getExtraProperty('ip')))
                        ->columnSpanFull(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('created_at')
                    ->label('زمان')
                    ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('دسته')
                    ->badge()
                    ->formatStateUsing(fn($state) => self::logNameLabel($state)),

                Tables\Columns\TextColumn::make('event')
                    ->label('عملیات')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'created' => 'success',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => self::eventLabel($state)),

                Tables\Columns\TextColumn::make('description')
                    ->label('توضیح')
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('انجام‌دهنده')
                    ->placeholder('سیستم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('causer.mobile')
                    ->label('موبایل')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('نوع رکورد')
                    ->formatStateUsing(fn($state) => $state ? class_basename($state) : '—'),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('log_name')
                    ->label('دسته')
                    ->options([
                        'auth' => 'ورود / احراز هویت',
                        'users' => 'کاربران',
                        'purchases' => 'خریدها',
                        'payment_transactions' => 'تراکنش‌های مالی',
                        'content_items' => 'محتوای آموزشی',
                        'videos' => 'ویدیوها',
                        'powerpoints' => 'پاورپوینت‌ها',
                    ]),

                Tables\Filters\SelectFilter::make('event')
                    ->label('نوع عملیات')
                    ->options([
                        'created' => 'ایجاد',
                        'updated' => 'ویرایش',
                        'deleted' => 'حذف',
                    ]),

            ])

            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
