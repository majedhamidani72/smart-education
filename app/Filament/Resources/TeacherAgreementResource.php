<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAgreementResource\Pages;
use App\Models\TeacherAgreement;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * قراردادهای پذیرفته‌شده (معلم/ادمین)
 * --------------------------------------------------------------------
 * فقط-نمایشی است — سند حقوقی/آدیت است و نباید از پنل دستکاری شود
 * (نگاه کنید به TeacherAgreement و AgreementService برای منطق ثبت).
 * فقط سوپرادمین به این لیست دسترسی دارد چون شامل IP و مرورگر
 * کاربران دیگر است.
 */
class TeacherAgreementResource extends Resource
{
    protected static ?string $model = TeacherAgreement::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'مدیریت کاربران';

    protected static ?string $navigationLabel = 'قراردادها';

    protected static ?string $modelLabel = 'قرارداد';

    protected static ?string $pluralModelLabel = 'قراردادهای پذیرفته‌شده';

    protected static ?int $navigationSort = 4;

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('مشخصات پذیرش')

                ->columns(3)

                ->schema([

                    TextEntry::make('teacher.name')
                        ->label('نام و نام خانوادگی'),

                    TextEntry::make('teacher.mobile')
                        ->label('شماره موبایل'),

                    TextEntry::make('agreement_type')
                        ->label('نوع قرارداد')
                        ->badge()
                        ->formatStateUsing(fn($state) => $state === 'admin' ? 'ادمین' : 'معلم'),

                    TextEntry::make('agreement_version')
                        ->label('نسخه‌ی قرارداد'),

                    TextEntry::make('accepted_at')
                        ->label('تاریخ پذیرش')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('ip_address')
                        ->label('آدرس IP')
                        ->placeholder('—')
                        ->copyable(),

                    TextEntry::make('user_agent')
                        ->label('دستگاه / مرورگر')
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('agreement_text')
                        ->label('متن دقیق پذیرفته‌شده')
                        ->placeholder('برای این پذیرش، متن ذخیره نشده (قبل از فعال‌سازی این قابلیت بوده است).')
                        ->columnSpanFull(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('accepted_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('کاربر')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('teacher.mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('agreement_type')
                    ->label('نوع قرارداد')
                    ->colors([
                        'primary' => 'teacher',
                        'warning' => 'admin',
                    ])
                    ->formatStateUsing(fn($state) => $state === 'admin' ? 'ادمین' : 'معلم'),

                Tables\Columns\TextColumn::make('agreement_version')
                    ->label('نسخه')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('accepted_at')
                    ->label('تاریخ پذیرش')
                    ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('agreement_text')
                    ->label('متن ذخیره‌شده؟')
                    ->boolean()
                    ->getStateUsing(fn(TeacherAgreement $record) => filled($record->agreement_text)),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('agreement_type')
                    ->label('نوع قرارداد')
                    ->options([
                        'teacher' => 'معلم',
                        'admin' => 'ادمین',
                    ]),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('print')
                    ->label('چاپ')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn(TeacherAgreement $record) => route('agreement.print', $record))
                    ->openUrlInNewTab(),

            ])

            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAgreements::route('/'),
            'view' => Pages\ViewTeacherAgreement::route('/{record}'),
        ];
    }
}
